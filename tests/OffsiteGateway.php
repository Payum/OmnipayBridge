<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\AbstractGateway;

class OffsiteGateway extends AbstractGateway
{
    public $returnOnPurchase;

    public $returnOnCompletePurchase;

    public function purchase()
    {
        return $this->returnOnPurchase;
    }

    public function completePurchase()
    {
        return $this->returnOnCompletePurchase;;
    }

    public function getName() {}
}
