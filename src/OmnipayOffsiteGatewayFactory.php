<?php
namespace Payum\OmnipayBridge;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\OmnipayBridge\Action\OffsiteCaptureAction;

/**
 * @deprecated since 1.0.0-BETA1
 */
class OmnipayOffsiteGatewayFactory extends OmnipayDirectGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.action.capture' => new OffsiteCaptureAction(),
        ]);

        return parent::populateConfig($config);
    }
}