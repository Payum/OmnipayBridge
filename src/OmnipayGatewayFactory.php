<?php
namespace Payum\OmnipayBridge;

use Omnipay\Common\Exception\OmnipayException;
use Omnipay\Common\GatewayFactory as OmnipayOmnipayGatewayFactory;
use Omnipay\Common\GatewayInterface;
use Omnipay\Omnipay;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\ConvertPaymentAction;
use Payum\OmnipayBridge\Action\OffsiteCaptureAction;
use Payum\OmnipayBridge\Action\StatusAction;

class OmnipayGatewayFactory extends GatewayFactory
{
    /**
     * @var string
     */
    private $omnipayGatewayTypeOrClass;

    /**
     * @var OmnipayOmnipayGatewayFactory|null
     */
    private $omnipayGatewayFactory;

    /**
     * {@inheritDoc}
     *
     * @param string $omnipayGatewayTypeOrClass
     * @param OmnipayOmnipayGatewayFactory|null $omnipayGatewayFactory
     */
    public function __construct($omnipayGatewayTypeOrClass = null, OmnipayOmnipayGatewayFactory $omnipayGatewayFactory = null, array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        parent::__construct($defaultConfig, $coreGatewayFactory);

        $this->omnipayGatewayTypeOrClass = $omnipayGatewayTypeOrClass;
        $this->omnipayGatewayFactory = $omnipayGatewayFactory ?: Omnipay::getFactory();
    }

    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.action.capture' => new CaptureAction(),
            'payum.action.capture_offsite' => new OffsiteCaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ]);

        if (false == $config['payum.api']) {
            //BC layer
            if ($config['options']) {
                $config->defaults($config['options']);
            }

            $config->defaults([
                'type' => $this->omnipayGatewayTypeOrClass,
            ]);

            // omnipay does not provide required options.
            $config['payum.required_options'] = ['type'];

            $gateway = null;
            if ($config['type']) {
                try {
                    /** @var GatewayInterface $gateway */
                    $gateway = $this->omnipayGatewayFactory->create($config['type']);
                } catch (OmnipayException $e) {
                    throw new LogicException(sprintf(
                        'Given omnipay gateway type %s or class is not supported. Supported: %s',
                        $config['type'],
                        implode(', ', $this->omnipayGatewayFactory->getSupportedGateways())
                    ), 0, $e);
                }

                $config['payum.default_options'] = array_replace(['testMode' => true], $gateway->getDefaultParameters());
                $config->defaults($config['payum.default_options']);
            }

            $config['payum.api'] = function (ArrayObject $config) use ($gateway) {
                $config->validateNotEmpty($config['payum.required_options']);

                $gateway->initialize((array)$config);

                return $gateway;
            };
        }
    }
}