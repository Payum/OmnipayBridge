<?php
namespace Payum\OmnipayBridge;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\OmnipayBridge\Action\OffsiteCaptureAction;

class OffsitePaymentFactory extends DirectPaymentFactory
{
    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config->defaults(array(
            'payum.action.capture' => new OffsiteCaptureAction(),
        ));

        return parent::createConfig((array) $config);
    }
}