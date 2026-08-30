<?php

namespace Spatie\MailcoachMailer\Concerns;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;

trait ReplacesHeaders
{
    private function replaceHeader(Email $email, HeaderInterface $header): void
    {
        $email->getHeaders()->remove($header->getName());
        $email->getHeaders()->add($header);
    }
}
