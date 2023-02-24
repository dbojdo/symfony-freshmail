<?php

namespace Symfony\Component\Mailer\Bridge\Freshmail\Tests\Transport;

use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Mailer\Bridge\Freshmail\Transport\FreshmailApiTransport;
use Symfony\Component\Mailer\Bridge\Freshmail\Transport\FreshmailTransportFactory;
use Symfony\Component\Mailer\Test\TransportFactoryTestCase;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

class FreshmailTransportFactoryTest extends TransportFactoryTestCase
{
    public function getFactory(): TransportFactoryInterface
    {
        return new FreshmailTransportFactory(null, new MockHttpClient(), new NullLogger());
    }

    public function supportsProvider(): iterable
    {
        yield [
            new Dsn('freshmail+api', 'default'),
            true,
        ];
    }

    public function createProvider(): iterable
    {
        $client = new MockHttpClient();
        $logger = new NullLogger();

        yield [
            new Dsn('freshmail+api', 'default', self::USER),
            new FreshmailApiTransport(self::USER, $client, null, $logger),
        ];

    }

    public function unsupportedSchemeProvider(): iterable
    {
        yield [
            new Dsn('freshmail+foo', 'default', self::USER),
            'The "freshmail+foo" scheme is not supported; supported schemes for mailer "freshmail" are: "freshmail+api".',
        ];
    }

    public function incompleteDsnProvider(): iterable
    {
        yield [new Dsn('freshmail+api', 'default')];
    }

}
