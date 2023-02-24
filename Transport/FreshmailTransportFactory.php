<?php

namespace Symfony\Component\Mailer\Bridge\Freshmail\Transport;

use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class FreshmailTransportFactory extends AbstractTransportFactory
{

    protected function getSupportedSchemes(): array
    {
        return ['freshmail+api'];
    }

    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $user = $this->getUser($dsn);

        if ('freshmail+api' === $scheme) {
            return (new FreshmailApiTransport($user, $this->client, $this->dispatcher, $this->logger));
        }

        throw new UnsupportedSchemeException($dsn, 'freshmail', $this->getSupportedSchemes());
    }
}
