# Raw capture 

In the [get it started](https://github.com/Payum/OmnipayBridge/blob/master/src/Resources/docs/get-it-started.md) we showed how to use the library with unified interface or in other words Order model. 
Sometimes you need completely custom solution.  

## config.php

Add a storage for the payment model;

```php
<?php
// config.php

// ...

$detailsClass = 'Payum\Core\Model\ArrayObject';

$storages = array(
    $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass),
    
    //put other storages
);
```

## prepare.php

Installation and configuration are same and we have to modify only a prepare part. 

Here you have to modify a `paymentName` value. Set it to `stripe_omnipay` or `paypal_omnipay` or any other you configure.
The rest remain the same as described basic [get it started](https://github.com/Payum/Core/blob/master/Resources/docs/get-it-started.md) documentation.
If you have to pass a credit card just add it to the Order like this. 

Credit Card example:
 
```php
<?php
// prepare.php

include 'config.php';

$paymentName = 'stripe_omnipay';

$storage = $payum->getStorage($detailsClass);

$details = $storage->createNew();
$details['amount'] = '10.00'; 
$details['currency'] = 'USD';
$details['card'] = new SensitiveValue(
    'number' => '4242424242424242', 
    'expiryMonth' => '6', 
    'expiryYear' => '2016', 
    'cvv' => '123',
);

$storage->update($details);

$captureToken = $tokenFactory->createCaptureToken($paymentName, $details, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

Offsite example:
 
```php
<?php
// prepare.php

include 'config.php';

$paymentName = 'paypal_omnipay';

$storage = $payum->getStorage($detailsClass);

$details = $storage->createNew();
$storage->update($details);

$captureToken = $tokenFactory->createCaptureToken($paymentName, $details, 'done.php');

$details['amount'] = '10.00'; 
$details['currency'] = 'USD';
$details['returnUrl'] = $captureToken->getTargetUrl();
$details['cancelUrl'] = $captureToken->getTargetUrl();
$storage->update($details);

header("Location: ".$captureToken->getTargetUrl());
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