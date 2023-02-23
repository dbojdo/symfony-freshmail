<?php

namespace Symfony\Component\Mailer\Bridge\Freshmail\Transport;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FreshmailApiTransport extends AbstractApiTransport
{

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {

    }

    public function __toString(): string
    {

    }
}
