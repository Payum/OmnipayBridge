<?php
namespace Payum\OmnipayBridge\Tests;

use Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory;

class OmnipayOffsiteGatewayFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGatewayFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\GatewayFactoryInterface'));
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
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage Given type Invalid is not supported. Try one of supported types: Dummy.
     */
    public function shouldThrowIfTypeNotValid()
    {
        $factory = new OmnipayOffsiteGatewayFactory();

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