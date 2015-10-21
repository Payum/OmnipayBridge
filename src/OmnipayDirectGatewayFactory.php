<?php
namespace Payum\OmnipayBridge;

use Omnipay\Omnipay;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\ConvertPaymentAction;
use Payum\OmnipayBridge\Action\StatusAction;

class OmnipayDirectGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @param array $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig());
        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ));

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