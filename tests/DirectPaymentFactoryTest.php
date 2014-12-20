<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface;
use Payum\OmnipayBridge\DirectPaymentFactory;

class DirectPaymentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementPaymentFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\DirectPaymentFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\PaymentFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new DirectPaymentFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreatePayment()
    {
        $factory = new DirectPaymentFactory();

        $payment = $factory->create(array('type' => 'Dummy', 'options' => array(
            'testMode' => true,
        )));

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeNotEmpty('apis', $payment);
        $this->assertAttributeNotEmpty('actions', $payment);

        $extensions = $this->readAttribute($payment, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentWithCustomGateway()
    {
        $factory = new DirectPaymentFactory();

        $payment = $factory->create(array(
            'payum.api' => $this->createGatewayMock(),
        ));

        $this->assertInstanceOf('Payum\Core\Payment', $payment);

        $this->assertAttributeNotEmpty('apis', $payment);
        $this->assertAttributeNotEmpty('actions', $payment);

        $extensions = $this->readAttribute($payment, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreatePaymentConfig()
    {
        $factory = new DirectPaymentFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The type fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new DirectPaymentFactory();

        $factory->create();
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Given type Invalid is not supported. Try one of supported types: Dummy.
     */
    public function shouldThrowIfTypeNotValid()
    {
        $factory = new DirectPaymentFactory();

        $factory->create(array('type' => 'Invalid'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock('Omnipay\Common\GatewayInterface');
    }
}