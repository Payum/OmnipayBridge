<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface as OmnipayGatewayInterface;
use Payum\Core\GatewayFactoryInterface;
use Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory;

class OmnipayOffsiteGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(OmnipayOffsiteGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayOffsiteGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGateway()
    {
        $factory = new OmnipayOffsiteGatewayFactory();

        $gateway = $factory->create(array('type' => 'Dummy', 'options' => array(
            'testMode' => true,
        )));

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);

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
        $factory = new OmnipayOffsiteGatewayFactory();

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
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The type fields are required.
     */
    public function shouldThrowIfRequiredOptionsNotPassed()
    {
        $factory = new OmnipayOffsiteGatewayFactory();

        $factory->create();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new OmnipayOffsiteGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     */
    public function shouldContainCaptureOffsiteActionAndMissCaptureAction()
    {
        $factory = new OmnipayOffsiteGatewayFactory();

        $config = $factory->createConfig(['type' => 'Dummy']);

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.action.capture_offsite', $config);
        $this->assertArrayNotHasKey('payum.action.capture', $config);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Given omnipay gateway type Invalid or class is not supported. Supported:
     */
    public function shouldThrowIfTypeNotValid()
    {
        $factory = new OmnipayOffsiteGatewayFactory();

        $factory->create(array('type' => 'Invalid'));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|OmnipayGatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(OmnipayGatewayInterface::class);
    }
}