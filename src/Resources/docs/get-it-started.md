# Get it started.

In this chapter we are going to talk about the most common task: purchase of a product using [Omnipay](https://github.com/omnipay/omnipay).
We assume you already read [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/omnipay-bridge:*@stable"
```

## config.php

We have to only add a the gateway factory. All the rest remain the same:

## config.php

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()

    // direct payment like Stripe or Authorize.Net

    ->addGateway('gatewayName', [
        'factory' => 'omnipay',
        'type' => 'stripe',
        'username' => 'REPLACE IT',
        'password' => 'REPLACE IT',
        'signature' => 'REPLACE IT',
        'testMode' => true
    ])

    // or offsite payment like Paypal ExpressCheckout

    ->addGateway('gatewayName', [
        'factory' => 'omnipay',
        'type' => 'paypal_express'
        'username' => 'REPLACE IT',
        'password' => 'REPLACE IT',
        'signature' => 'REPLACE IT',
        'testMode' => true
    ])

    ->getPayum()
;
```

## prepare.php

Here you have to modify the `gatewayName` value. Set it to `paypal_express_checkout` or `stripe`. The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.

## Next

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).