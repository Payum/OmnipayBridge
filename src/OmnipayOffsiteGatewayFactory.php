<?php
namespace Payum\OmnipayBridge;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\OmnipayBridge\Action\OffsiteCaptureAction;

class OmnipayOffsiteGatewayFactory extends OmnipayDirectGatewayFactory
{
    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults(array(
            'payum.action.capture' => new OffsiteCaptureAction(),
        ));

        return parent::createConfig((array) $config);
    }
}