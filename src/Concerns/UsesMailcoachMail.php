<?php

namespace Spatie\MailcoachMailer\Concerns;

use Illuminate\Mail\Mailable;
use Spatie\MailcoachMailer\Headers\FakeHeader;
use Spatie\MailcoachMailer\Headers\GoogleAnalyticsCampaignHeader;
use Spatie\MailcoachMailer\Headers\GoogleAnalyticsDomainsHeader;
use Spatie\MailcoachMailer\Headers\MailerHeader;
use Spatie\MailcoachMailer\Headers\ReplacementHeader;
use Spatie\MailcoachMailer\Headers\StoreContentHeader;
use Spatie\MailcoachMailer\Headers\TransactionalMailHeader;
use Spatie\MailcoachMailer\Headers\WebhookHeader;
use Symfony\Component\Mime\Email;

/** @mixin Mailable */
trait UsesMailcoachMail
{
    private bool $usingMailcoachMail = false;

    public function mailcoachMail(string $mailName, array $replacements = [], ?string $mailer = null, ?bool $fake = null): self
    {
        $this->usingMailcoachMail = true;

        $this->html = 'use-mailcoach-mail';

        $this->replacing($replacements);
        $this->usingMailer($mailer);
        $this->faking($fake);

        $this->withSymfonyMessage(function (Email $email) use ($mailName) {
            $transactionalHeader = new TransactionalMailHeader($mailName);

            if ($email->getHeaders()->has($transactionalHeader->getName())) {
                $email->getHeaders()->remove($transactionalHeader->getName());
            }

            $email->getHeaders()->add($transactionalHeader);
        });

        return $this;
    }

    public function usingMailer(?string $mailer): self
    {
        if (! $mailer) {
            return $this;
        }

        $this->withSymfonyMessage(function (Email $email) use ($mailer) {
            $mailerHeader = new MailerHeader($mailer);

            if ($email->getHeaders()->has($mailerHeader->getName())) {
                $email->getHeaders()->remove($mailerHeader->getName());
            }

            $email->getHeaders()->add($mailerHeader);
        });

        return $this;
    }

    public function replacing(array|string $key, string|array|null $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $realKey => $value) {
                $this->replacing($realKey, $value);
            }

            return $this;
        }

        $this->withSymfonyMessage(function (Email $email) use ($key, $value) {
            $email->getHeaders()->add(new ReplacementHeader($key, $value));
        });

        return $this;
    }

    public function faking(?bool $value): self
    {
        if (! $value) {
            return $this;
        }

        $this->withSymfonyMessage(function (Email $email) use ($value) {
            $fakeHeader = new FakeHeader($value);

            if ($email->getHeaders()->has($fakeHeader->getName())) {
                $email->getHeaders()->remove($fakeHeader->getName());
            }

            $email->getHeaders()->add($fakeHeader);
        });

        return $this;
    }

    protected function buildSubject($message): self
    {
        if (! $this->usingMailcoachMail) {
            return parent::buildSubject($message);
        }

        if ($this->subject) {
            $message->subject($this->subject);
        }

        return $this;
    }

    public function storingContent(bool $value): self
    {
        $this->withSymfonyMessage(function (Email $email) use ($value) {
            $storeContentHeader = new StoreContentHeader($value);

            if ($email->getHeaders()->has($storeContentHeader->getName())) {
                $email->getHeaders()->remove($storeContentHeader->getName());
            }

            $email->getHeaders()->add($storeContentHeader);
        });

        return $this;
    }

    public function usingGoogleAnalytics(string $campaign, array $domains): self
    {
        $this->withSymfonyMessage(function (Email $email) use ($campaign, $domains) {
            $campaignHeader = new GoogleAnalyticsCampaignHeader($campaign);
            $domainsHeader = new GoogleAnalyticsDomainsHeader($domains);

            foreach ([$campaignHeader, $domainsHeader] as $header) {
                if ($email->getHeaders()->has($header->getName())) {
                    $email->getHeaders()->remove($header->getName());
                }

                $email->getHeaders()->add($header);
            }
        });

        return $this;
    }

    public function usingWebhook(string $webhookUrl): self
    {
        $this->withSymfonyMessage(function (Email $email) use ($webhookUrl) {
            $webhookHeader = new WebhookHeader($webhookUrl);

            if ($email->getHeaders()->has($webhookHeader->getName())) {
                $email->getHeaders()->remove($webhookHeader->getName());
            }

            $email->getHeaders()->add($webhookHeader);
        });

        return $this;
    }
}
