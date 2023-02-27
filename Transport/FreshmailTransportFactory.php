<?php

namespace Symfony\Component\Mailer\Bridge\Freshmail\Transport;

use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class FreshmailTransportFactory extends AbstractTransportFactory
{

    protected function getSupportedSchemes(): array
    {

    }

    public function create(Dsn $dsn): TransportInterface
    {

    }
}
