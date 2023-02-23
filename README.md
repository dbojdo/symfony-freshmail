# Symfony Freshmail Mailer Bridge

Provides Freshmail integration for Symfony Mailer.

## Instalation

### via Composer
```sh
composer require goosfraba/freshmail-mailer
```

## Container configuration

Register a new transport factory in your `services.yaml`

```
services:
    Goosfraba\Freshmail\Transport\FreshmailTransportFactory:
      tags:
          - { name: mailer.transport_factory }
```

## Configure transport

Configure the `MAILER_DSN` (usually in your `.env`) 

```
MAILER_DSN=freshmail+api://your-token@default
```
