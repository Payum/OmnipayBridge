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
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->defaults(array(
            'payum.action.capture' => new OffsiteCaptureAction(),
        ));


        parent::build($payment, $config);
    }
}