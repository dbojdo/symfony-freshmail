<?php

namespace Goosfraba\Freshmail\Transport;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractApiTransport;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class FreshmailApiTransport extends AbstractApiTransport
{
    private string $token;

    public function __construct(
        string $token,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        $this->token = $token;

        parent::__construct($client, $dispatcher, $logger);
    }

    protected function doSendApi(SentMessage $sentMessage, Email $email, Envelope $envelope): ResponseInterface
    {
        $response = $this->client->request(
            'POST',
            'https://api.freshmail.com/v3/messaging/emails',
            [
                'auth_bearer' => $this->token,
                'json' => $this->preparePayload($email, $envelope),
            ]
        );

        try {
            $statusCode = $response->getStatusCode();
            $result = $response->toArray(false);
        } catch (DecodingExceptionInterface) {
            throw new HttpTransportException(
                'Unable to send an email: '.$response->getContent(false).sprintf(' (code %d).', $statusCode), $response
            );
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Could not reach the remote Freshmail server.', $response, 0, $e);
        }

        if (201 !== $statusCode) {
            throw new HttpTransportException('Unable to send an email: '. $result['errors'][0], $response);
        }

        $firstRecipient = reset($result);
        $sentMessage->setMessageId($firstRecipient['id']);

        return $response;
    }

    public function __toString(): string
    {
        return 'freshmail+api://api.freshmail.com';
    }

    private function preparePayload(Email $email, Envelope $envelope): array
    {
        return  [
            'subject' => $email->getSubject(),
            'from' => [
                'name' => $envelope->getSender()->getName(),
                'email' => $envelope->getSender()->getAddress(),
            ],
            'recipients' => $this->prepareRecipients($envelope),
            'contents' => [
                [
                    'type'=> 'text/html',
                    'body' => $email->getHtmlBody()
                ]
            ],
            'attachments' => $this->prepareAttachments($email)
        ];
    }

    protected function prepareRecipients(Envelope $envelope): array
    {
        $recipients = [];
        foreach ($envelope->getRecipients() as $recipient)
        {
            $recipientPayload = [
                'email' => $recipient->getAddress(),
                'name' => $recipient->getName(),
            ];

            $recipients[] = $recipientPayload;
        }

        return $recipients;
    }

    protected function prepareAttachments(Email $email): ?array
    {
        $attachments = [];

        foreach ($email->getAttachments() as $attachment)
        {
            $headers = $attachment->getPreparedHeaders();

            $att = [
                'content' => $attachment->bodyToString(),
                'name' => $headers->getHeaderParameter('Content-Disposition', 'name'),
            ];

            $attachments[] = $att;

        }
        return empty($attachments) ? null: $attachments;
    }
}
