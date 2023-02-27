# Symfony Freshmail Mailer Bridge

Provides Freshmail integration for Symfony Mailer.

## Instalation
### via Composer
```
    composer require symfony/freshmail-mailer
```

## Usage

```php
    use Symfony\Component\Mailer\Bridge\Freshmail\Transport\FreshmailApiTransport;

    $mailer = new FreshmailApiTransport('FRESHMAIL_API_KEY');

    $email = new Email();
    
    $email->subject('Hello!')
          ->to(new Address('to@test.com', 'TO'))
          ->from(new Address('from@test.com', 'FROM'))
          ->html('<h1>Welcome</h1>');

    $mailer->send($email);
```
