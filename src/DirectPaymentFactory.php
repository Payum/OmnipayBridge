<?php
namespace Payum\OmnipayBridge;

use Omnipay\Omnipay;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;
use Payum\OmnipayBridge\Action\CaptureAction;
use Payum\OmnipayBridge\Action\FillOrderDetailsAction;
use Payum\OmnipayBridge\Action\StatusAction;

class DirectPaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->corePaymentFactory->createConfig());
        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['options.required'] = array('type');

            $config['payum.api.gateway'] = function(ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

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