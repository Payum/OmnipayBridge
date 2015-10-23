<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\AbstractGateway;

class CreditCardGateway extends AbstractGateway
{
    public $returnOnPurchase;

    public function purchase()
    {
        return $this->returnOnPurchase;
    }

    // this type of gateways do not have this method.
    //public function completePurchase() {}

    public function getName() {}
}
