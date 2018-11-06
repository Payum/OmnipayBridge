<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# OmnipayBridge

[![Build Status](https://travis-ci.org/Payum/OmnipayBridge.png?branch=master)](https://travis-ci.org/Payum/OmnipayBridge) [![Total Downloads](https://poser.pugx.org/payum/omnipay-bridge/d/total.png)](https://packagist.org/packages/payum/omnipay-bridge) [![Latest Stable Version](https://poser.pugx.org/payum/omnipay-bridge/version.png)](https://packagist.org/packages/payum/omnipay-bridge)

[Omnipay](https://github.com/adrianmacneil/omnipay) created by [Adrian Macneil](http://adrianmacneil.com/). The lib provides unified api for 25+ gateway gateways. Plus, it simple, has unified, consistent API and fully covered with tests.
This bridge allows you to use omnipay gateways but in payum like way.

## Why should you use this bridge?

Here's an [example from omnipay's repository](https://github.com/thephpleague/omnipay#tldr):

```php
<?php
use Omnipay\Omnipay;

$gateway = Omnipay::create('Stripe');
$gateway->setApiKey('abc123');

$formData = ['number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2016', 'cvv' => '123'];
$response = $gateway->purchase(['amount' => '10.00', 'currency' => 'USD', 'card' => $formData])->send();

if ($response->isSuccessful()) {
    // payment was successful: update database
    print_r($response);
} elseif ($response->isRedirect()) {
    // redirect to offsite payment gateway
    $response->redirect();
} else {
    // payment failed: display message to customer
    echo $response->getMessage();
}
```

and this is same code but done with the bridge:

```php
<?php
use Payum\Core\PayumBuilder;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Model\ArrayObject;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('stripe', ['factory' => 'omnipay_stripe', 'apiKey' => 'abc123'])
    ->getPayum()
;

$card = ['number' => '4242424242424242', 'expiryMonth' => '6', 'expiryYear' => '2016', 'cvv' => '123'];
$payment = new ArrayObject(['amount' => '10.00', 'currency' => 'USD', 'card' => $card]);

if ($reply = $payum->getGateway('stripe')->execute(new Capture($payment), true)) {
    // convert reply to http response
}

$payum->getGateway('stripe')->execute($status = new GetHumanStatus($payment));
if ($status->isCaptured()) {
    // success
}
```

Well more or less same amount of code but with the bridge you get more out of the box:

* Return\Cancel urls are generated just of the box. The urls are unique and do not expose any sensitive information.
* If you do not pass credit card, Payum asks a user for it, showing the page with the form.
* You can use Payum's packages for Symfony,Laravel,Silex,Zend,Yii with the bridge.
* Storages. Your payment is already stored on the filesystem. We advice not to use this storage in prod.
* The payment model contains all the information we were able to get from omnipay. Just use it.
* Payum abstracts workflow. It knows when Omnipay's `purchase` or `purchaseComplete` methods should be used.
* Credit card details are protected from accidental storing on your side.
* Using the builder you can overwrite any part you want, or add a Payum extension.

## Resources

* [Site](https://payum.forma-pro.com/)
* [Documentation](https://github.com/Payum/Payum/blob/master/src/Payum/Core/Resources/docs/index.md)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## Developed by Forma-Pro

Forma-Pro is a full stack development company which interests also spread to open source development. 
Being a team of strong professionals we have an aim an ability to help community by developing cutting edge solutions in the areas of e-commerce, docker & microservice oriented architecture where we have accumulated a huge many-years experience. 
Our main specialization is Symfony framework based solution, but we are always looking to the technologies that allow us to do our job the best way. We are committed to creating solutions that revolutionize the way how things are developed in aspects of architecture & scalability.

If you have any questions and inquires about our open source development, this product particularly or any other matter feel free to contact at opensource@forma-pro.com

## License

OmnipayBridge is released under the [MIT License](LICENSE).
