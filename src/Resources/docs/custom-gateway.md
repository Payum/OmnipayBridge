# Custom gateway.

In this chapter we are going to show how you can create a payment with custom [Omnipay](https://github.com/omnipay/omnipay) gateway. It may be community created gateway or you own.
We assume you already read [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) from core.
Here we just show you modifications you have to put to the files shown there.

## config.php

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

$stripeGateway = new \Omnipay\Stripe\Gateway();
$stripeGateway->setApiKey('REPLACE IT');
$stripeGateway->setTestMode(true);

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addGateway('gatewayName', [
        'factory' => 'Omnipay',
        'payum.api' => $stripeGateway,
    ])
;
```

## Next

* [Core's Get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md).
* [The architecture](https://github.com/Payum/Core/blob/master/Resources/docs/the-architecture.md).
* [Supported gateways](https://github.com/Payum/Core/blob/master/Resources/docs/supported-gateways.md).
* [Storages](https://github.com/Payum/Core/blob/master/Resources/docs/storages.md).

Back to [index](index.md).