<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\OmnipayBridge\OmnipayDirectGatewayFactory;

class OmnipayDirectGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(OmnipayDirectGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayDirectGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new OmnipayDirectGatewayFactory();

        $gateway = $factory->create(array('type' => 'Dummy', 'options' => array(
            'testMode' => true,
        )));

        $this->assertInstanceOf(Gateway::class, $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithCustomGateway()
    {
        $factory = new OmnipayDirectGatewayFactory();

        $gateway = $factory->create(array(
            'payum.api' => $this->createGatewayMock(),
        ));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new OmnipayDirectGatewayFactory();

        $config = $factory->createConfig(['type' => 'Dummy']);

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldContainCaptureActionAndMissCaptureOffsiteAction()
    {
        $factory = new OmnipayDirectGatewayFactory();

        $config = $factory->createConfig(['type' => 'Dummy']);

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.action.capture', $config);
        $this->assertArrayNotHasKey('payum.action.capture_offsite', $config);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The type fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new OmnipayDirectGatewayFactory();

        $factory->create();
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Given omnipay gateway type Invalid or class is not supported. Supported:
     */
    public function shouldThrowIfTypeNotValid()
    {
        $factory = new OmnipayDirectGatewayFactory();

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