<?php
namespace Payum\OmnipayBridge;

use Omnipay\Omnipay;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\ConvertPaymentAction;
use Payum\OmnipayBridge\Action\StatusAction;

/**
 * @deprecated since 1.0.0-BETA1
 */
class OmnipayDirectGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'type' => null,
                'options' => [],
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('type');

            $config['payum.api.gateway'] = function(ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

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

                return $gateway;
            };
        }

        return (array) $config;
    }
}