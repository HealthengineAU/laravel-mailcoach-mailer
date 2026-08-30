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
    use ReplacesHeaders;

    private bool $usingMailcoachMail = false;

    public function mailcoachMail(string $mailName, array $replacements = [], ?string $mailer = null, ?bool $fake = null): self
    {
        $this->usingMailcoachMail = true;

        $this->html = 'use-mailcoach-mail';

        $this->replacing($replacements);
        $this->usingMailer($mailer);
        $this->faking($fake);

        $this->withSymfonyMessage(function (Email $email) use ($mailName) {
            $this->replaceHeader($email, new TransactionalMailHeader($mailName));
        });

        return $this;
    }

    public function usingMailer(?string $mailer): self
    {
        if (! $mailer) {
            return $this;
        }

        $this->withSymfonyMessage(function (Email $email) use ($mailer) {
            $this->replaceHeader($email, new MailerHeader($mailer));
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
            $this->replaceHeader($email, new FakeHeader($value));
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
            $this->replaceHeader($email, new StoreContentHeader($value));
        });

        return $this;
    }

    public function usingGoogleAnalytics(string $campaign, array $domains): self
    {
        $campaignHeader = new GoogleAnalyticsCampaignHeader($campaign);
        $domainsHeader = new GoogleAnalyticsDomainsHeader($domains);

        $this->withSymfonyMessage(function (Email $email) use ($campaignHeader, $domainsHeader) {
            $this->replaceHeader($email, $campaignHeader);
            $this->replaceHeader($email, $domainsHeader);
        });

        return $this;
    }

    public function usingWebhook(string $webhookUrl): self
    {
        $this->withSymfonyMessage(function (Email $email) use ($webhookUrl) {
            $this->replaceHeader($email, new WebhookHeader($webhookUrl));
        });

        return $this;
    }
}
