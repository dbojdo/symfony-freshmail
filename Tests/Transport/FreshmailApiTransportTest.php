<?php

namespace Goosfraba\Freshmail\Tests\Transport;

use Goosfraba\Freshmail\Transport\FreshmailApiTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FreshmailApiTransportTest extends TestCase
{
    /**
     * @dataProvider getTransportData
     */
    public function testToString(FreshmailApiTransport $transport, string $expected)
    {
        $this->assertSame($expected, (string)$transport);
    }

    public static function getTransportData()
    {
        return [
            [
                new FreshmailApiTransport('KEY'),
                'freshmail+api://api.freshmail.com',
            ]
        ];
    }

    public function testSend()
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
            $this->assertSame('POST', $method);
            $this->assertSame('https://api.freshmail.com/v3/messaging/emails', $url);

            $body = json_decode($options['body'], true);
            $this->assertSame('Hello from Freshmail!', $body['subject']);
            $this->assertSame('Hubert', $body['from']['name']);
            $this->assertSame('hubert@test.com', $body['from']['email']);
            $this->assertSame('Test Name', $body['recipients'][0]['name']);
            $this->assertSame('testName@test.com', $body['recipients'][0]['email']);
            $this->assertSame('<p>Welcome</p>', $body['contents'][0]['body']);

            return new MockResponse(json_encode([['id' => 'foobar']]), [
                'http_code' => 201,
            ]);
        });

        $transport = new FreshmailApiTransport('KEY', $client);

        $mail = new Email();
        $mail->subject('Hello from Freshmail!')
            ->to(new Address('testName@test.com', 'Test Name'))
            ->from(new Address('hubert@test.com', 'Hubert'))
            ->html('<p>Welcome</p>');

        $message = $transport->send($mail);

        $this->assertSame('foobar', $message->getMessageId());
    }

    public function testSendThrowsForErrorResponse()
    {
        $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
            return new MockResponse(json_encode(['errors' => ["message error"]]), [
                'http_code' => 418,
            ]);
        });

        $transport = new FreshmailApiTransport('KEY', $client);

        $mail = new Email();
        $mail->subject('Hello from Freshmail!')
            ->to(new Address('testName@test.com', 'Test Name'))
            ->from(new Address('hubert@test.com', 'Hubert'))
            ->html('<p>Welcome</p>');

        $this->expectException(HttpTransportException::class);
        $this->expectExceptionMessage('Unable to send an email: message error');
        $transport->send($mail);
    }

}
