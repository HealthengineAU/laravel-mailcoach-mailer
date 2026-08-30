<?php

namespace Spatie\MailcoachMailer\Tests\TestSupport\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\MailcoachMailer\Concerns\UsesMailcoachMail;

class RepeatedOptionsMail extends Mailable
{
    use Queueable, SerializesModels, UsesMailcoachMail;

    public function envelope()
    {
        return new Envelope(
            from: 'from@example.com',
            subject: 'Repeated Options Mail',
        );
    }

    public function build()
    {
        $this
            ->mailcoachMail('mail-name')
            ->storingContent(false)
            ->storingContent(true)
            ->usingGoogleAnalytics('old-campaign', ['old.example.com'])
            ->usingGoogleAnalytics('campaign-name', ['example.com'])
            ->usingWebhook('https://example.com/old')
            ->usingWebhook('https://spatie.be/');
    }
}
