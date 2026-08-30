<p align="center">
    <a href="https://evomark.co.uk" target="_blank" alt="Link to evoMark's website">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--dark.svg">
          <source media="(prefers-color-scheme: light)" srcset="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--light.svg">
          <img alt="evoMark company logo" src="https://evomark.co.uk/wp-content/uploads/static/evomark-logo--light.svg" width="500">
        </picture>
    </a>
</p>

<p align="center">
    <a href="https://packagist.org/packages/evo-mark/evo-laravel-brevo">
        <img src="https://img.shields.io/packagist/v/evo-mark/evo-laravel-brevo?logo=packagist&logoColor=white" alt="Build status" />
    </a>
    <a href="https://packagist.org/packages/evo-mark/evo-laravel-brevo">
        <img src="https://img.shields.io/packagist/dt/evo-mark/evo-laravel-brevo" alt="Total Downloads">
    </a>
    <a href="https://packagist.org/packages/evo-mark/evo-laravel-brevo">
        <img src="https://img.shields.io/packagist/l/evo-mark/evo-laravel-brevo" alt="Licence">
    </a>
</p>

# Laravel Brevo

```sh
composer require evo-mark/evo-laravel-brevo
```

## .env file

```
MAIL_MAILER=brevo
MAIL_FROM_ADDRESS="example@example.com"
MAIL_FROM_NAME="Example"
BREVO_API_KEY="<your api token here>"
```

## config/mail.php

```
    'mailers' => [
        'brevo' => [
            'transport' => 'brevo',
        ],
    ]
```
