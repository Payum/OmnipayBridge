<?php
namespace Payum\OmnipayBridge\Tests;

use Omnipay\Common\GatewayInterface as OmnipayGatewayInterface;
use Payum\Core\Gateway;
use Payum\Core\GatewayFactoryInterface;
use Payum\OmnipayBridge\OmnipayGatewayFactory;

class OmnipayGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass(OmnipayGatewayFactory::class);

        $this->assertTrue($rc->implementsInterface(GatewayFactoryInterface::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new OmnipayGatewayFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithTypeGivenInConstructor()
    {
        $factory = new OmnipayGatewayFactory('Dummy');

        $gateway = $factory->create([]);

        $this->assertInstanceOf(Gateway::class, $gateway);

        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');
        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayWithTypeGivenInConfig()
    {
        $factory = new OmnipayGatewayFactory();

        $gateway = $factory->create(['type' => 'Dummy']);

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
        $factory = new OmnipayGatewayFactory();

        $gateway = $factory->create([
            'payum.api' => $this->createGatewayMock(),
        ]);

        $this->assertInstanceOf(Gateway::class, $gateway);

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
        $factory = new OmnipayGatewayFactory();

        $factory->create();
    }

    /**
     * @test
     */
    public function shouldAllowCreateGatewayConfig()
    {
        $factory = new OmnipayGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Given omnipay gateway type Invalid or class is not supported. Supported:
     */
    public function shouldThrowIfTypeNotValid()
    {
        $factory = new OmnipayGatewayFactory();

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