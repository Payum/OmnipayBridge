<?php
namespace Payum\OmnipayBridge;

use Omnipay\Omnipay;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory;
use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\FillOrderDetailsAction;
use Payum\OmnipayBridge\Action\StatusAction;

class DirectPaymentFactory extends PaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        if (false == $config['payum.api.gateway']) {
            $config->validateNotEmpty(array('type', 'options'));

            $gatewayFactory = Omnipay::getFactory();
            $gatewayFactory->find();

            $supportedTypes = $gatewayFactory->all();
            if (false == in_array($config['type'], $supportedTypes)) {
                throw new LogicException(sprintf(
                    'Given type %s is not supported. Try one of supported types: %s.',
                    $config['type'],
                    implode(', ', $supportedTypes)
                ));
            }

            $gateway = $gatewayFactory->create($config['type']);
            foreach ($config['options'] as $name => $value) {
                $gateway->{'set'.strtoupper($name)}($value);
            }

            $config['payum.api.gateway'] = $gateway;
        }

        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.status' => new StatusAction(),
        ));
    }
}