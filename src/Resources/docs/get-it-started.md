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

```php
<?php
//config.php

// ...

// direct payment like Stripe or Authorize.Net

$directOmnipayFactory = new Payum\OmnipayBridge\DirectGatewayFactory();
$gateways['stripe_omnipay'] = $directOmnipayFactory->create(array(
    'type' => 'Stripe',
    'options' => array('apiKey' => 'REPLACE IT', 'testMode' => true),
));


// or offsite payment like Paypal ExpressCheckout

$offsiteOmnipayFactory = new Payum\OmnipayBridge\OffsiteGatewayFactory();
$gateways['paypal_omnipay'] = $offsiteOmnipayFactory->create(array(
    'type' => 'PayPal_Express',
    'options' => array(
        'username' => 'REPLACE IT', 
        'password' => 'REPLACE IT',
        'signature' => 'REPLACE IT',
        'testMode' => true
    ),
));
```

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `stripe_omnipay` or `paypal_omnipay` or any other you configure.
The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.
If you have to pass a credit card just add it to the Order like this:

```
use Payum\Core\Model\CreditCard;

// ...

$card = new CreditCard();
$card->setNumber('4111111111111111');
$card->setExpireAt(new \DateTime('2018-10-10'));
$card->setSecurityCode(123);

$payment->setCreditCard($card);
```

## Next

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).
* [Capture script](https://github.com/Payum/Core/blob/master/Resources/docs/capture-script.md).
* [Authorize script](https://github.com/Payum/Core/blob/master/Resources/docs/authorize-script.md).
* [Done script](https://github.com/Payum/Core/blob/master/Resources/docs/done-script.md).

Back to [index](index.md).