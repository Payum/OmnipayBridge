<?php
namespace Payum\OmnipayBridge;
use Payum\Core\Bridge\Spl\ArrayObject;

/**
 * @deprecated since 1.0.0-BETA1
 */
class OmnipayDirectGatewayFactory extends OmnipayGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        parent::populateConfig($config);

        unset($config['payum.action.capture_offsite']);
    }
}