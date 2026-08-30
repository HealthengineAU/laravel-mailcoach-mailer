<?php

namespace Spatie\MailcoachMailer\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Arr;
use Spatie\MailcoachMailer\Concerns\ReplacesHeaders;
use Spatie\MailcoachMailer\Headers\FakeHeader;
use Spatie\MailcoachMailer\Headers\GoogleAnalyticsCampaignHeader;
use Spatie\MailcoachMailer\Headers\GoogleAnalyticsDomainsHeader;
use Spatie\MailcoachMailer\Headers\MailerHeader;
use Spatie\MailcoachMailer\Headers\ReplacementHeader;
use Spatie\MailcoachMailer\Headers\StoreContentHeader;
use Spatie\MailcoachMailer\Headers\TransactionalMailHeader;
use Spatie\MailcoachMailer\Headers\WebhookHeader;
use Symfony\Component\Mime\Email;

class MailcoachMessage extends MailMessage
{
    use ReplacesHeaders;

    public string $mailName;

    public array $replacements = [];

    public bool $fake = false;

    public bool $storeContent = true;

    public ?string $googleAnalyticsCampaign = null;

    public array $googleAnalyticsDomains = [];

    public ?string $webhook = null;

    public function usingMail(string $mailName): self
    {
        $this->mailName = $mailName;
        $this->subject = '<empty-subject>';

        $this->withSymfonyMessage(function (Email $email) use ($mailName) {
            $this->replaceHeader($email, new TransactionalMailHeader($mailName));

            if ($email->getSubject() === '<empty-subject>') {
                $email->subject('');
            }
        });

        return $this;
    }

    public function usingMailer(string $mailer): self
    {
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

        Arr::set($this->replacements, $key, $value);

        $this->withSymfonyMessage(function (Email $email) use ($key, $value) {
            $email->getHeaders()->add(new ReplacementHeader($key, $value));
        });

        return $this;
    }

    public function faking(bool $value): self
    {
        $this->fake = $value;

        $this->withSymfonyMessage(function (Email $email) use ($value) {
            $this->replaceHeader($email, new FakeHeader($value));
        });

        return $this;
    }

    public function storingContent(bool $value): self
    {
        $this->storeContent = $value;

        $this->withSymfonyMessage(function (Email $email) use ($value): void {
            $this->replaceHeader($email, new StoreContentHeader($value));
        });

        return $this;
    }

    public function usingGoogleAnalytics(string $campaign, array $domains): self
    {
        $campaignHeader = new GoogleAnalyticsCampaignHeader($campaign);
        $domainsHeader = new GoogleAnalyticsDomainsHeader($domains);

        $this->googleAnalyticsCampaign = $campaign;
        $this->googleAnalyticsDomains = $domains;

        $this->withSymfonyMessage(function (Email $email) use ($campaignHeader, $domainsHeader): void {
            $this->replaceHeader($email, $campaignHeader);
            $this->replaceHeader($email, $domainsHeader);
        });

        return $this;
    }

    public function usingWebhook(string $webhookUrl): self
    {
        $this->webhook = $webhookUrl;

        $this->withSymfonyMessage(function (Email $email) use ($webhookUrl): void {
            $this->replaceHeader($email, new WebhookHeader($webhookUrl));
        });

        return $this;
    }
}
