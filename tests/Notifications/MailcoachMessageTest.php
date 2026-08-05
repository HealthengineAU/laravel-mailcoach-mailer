<?php

use Illuminate\Support\Facades\Notification;
use Spatie\MailcoachMailer\Notifications\MailcoachMessage;
use Spatie\MailcoachMailer\Tests\TestSupport\Notifications\TestNotification;

beforeEach(function () {
    config()->set('mail.default', 'mailcoach');

    config()->set('mail.mailers.mailcoach', [
        'transport' => 'mailcoach',
        'domain' => 'test.mailcoach.app',
        'token' => 'fake-token',
    ]);
});

it('can send a notification that makes use of a mailcoach mail template', function () {
    expectResponse(function (string $method, string $url, array $options) {
        expect($url)->toBe('https://test.mailcoach.app/api/transactional-mails/send');
        expect($method)->toBe('POST');

        expect($options['headers'][1])->toBe('Authorization: Bearer fake-token');

        $body = json_decode($options['body'], true);
        expect($body['from'])->toBe('from@example.com');
        expect($body['to'])->toBe('to@example.com');
        expect($body['mail_name'])->toBe('mail-name');

        expect($body['replacements'])->toBe([
            'singleName' => 'singleValue',
            'multipleName' => 'multipleValue',
        ]);
    });

    Notification::route('mail', 'to@example.com')->notify(new TestNotification);
});

it('does not prefill subject from a notification', function () {
    expectResponse(function (string $method, string $url, array $options) {
        $body = json_decode($options['body'], true);
        expect($body['subject'])->toBe('');
    });

    Notification::route('mail', 'to@example.com')->notify(new TestNotification);
});

it('can send a notification that uses a different mailer', function () {
    expectResponse(function (string $method, string $url, array $options) {
        expect($url)->toBe('https://test.mailcoach.app/api/transactional-mails/send');
        expect($method)->toBe('POST');

        expect($options['headers'][1])->toBe('Authorization: Bearer fake-token');

        $body = json_decode($options['body'], true);
        expect($body['mailer'])->toBe('transactional-mailer');
    });

    Notification::route('mail', 'to@example.com')->notify(new TestNotification);
});

it('can inspect mailcoach message', function () {
    $mailcoachMessage = (new TestNotification)->toMail();

    expect($mailcoachMessage->mailName)->toBe('mail-name');
    expect($mailcoachMessage->replacements)->toBe([
        'singleName' => 'singleValue',
        'multipleName' => 'multipleValue',
    ]);
    expect($mailcoachMessage->googleAnalyticsCampaign)->toBe('campaign-name');
    expect($mailcoachMessage->googleAnalyticsDomains)->toBe(['example.com', 'example.org']);
    expect($mailcoachMessage->webhook)->toBe('https://spatie.be/');
});

it('can send a notification that disables content storage', function () {
    expectResponse(function (string $method, string $url, array $options) {
        $body = json_decode($options['body'], true);

        expect($body['store_content'])->toBe('0');
    });

    Notification::route('mail', 'to@example.com')->notify(new TestNotification());
});

it('can send a notification that sets Google Analytics campaign and domains', function () {
    expectResponse(function (string $method, string $url, array $options) {
        $body = json_decode($options['body'], true);

        expect($body['google_analytics_campaign'])->toBe('campaign-name');
        expect($body['google_analytics_domains'])->toBe(['example.com', 'example.org']);
    });

    Notification::route('mail', 'to@example.com')->notify(new TestNotification());
});

it('can send a notification that sets webhook', function () {
    expectResponse(function (string $method, string $url, array $options) {
        $body = json_decode($options['body'], true);

        expect($body['webhook'])->toBe('https://spatie.be/');
    });

    Notification::route('mail', 'to@example.com')->notify(new TestNotification());
});
