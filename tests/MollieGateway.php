<?php

namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\AbstractGateway;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class MollieGateway extends AbstractGateway
{
    public $returnFetchTransaction;

    public function getName() {}

    public function fetchTransaction()
    {
        return $this->returnFetchTransaction;
    }
}
