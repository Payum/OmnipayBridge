<?php
namespace Payum\OmnipayBridge\Tests\Functional;

use Payum\Core\Request\GetHumanStatus;
use Payum\OmnipayBridge\OmnipayOffsiteGatewayFactory;
use Payum\Core\Request\Capture;
use Payum\OmnipayBridge\OmnipayGatewayFactory;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldFinishSuccessfully()
    {
        $factory = new OmnipayGatewayFactory('Dummy');

        $gateway = $factory->create([]);

        $date = new \DateTime('now + 2 year');

        $capture = new Capture(array(
            'amount' => '1000.00',
            'card' => array(
                'number' => '4242424242424242', // must be authorized
                'cvv' => 123,
                'expiryMonth' => 6,
                'expiryYear' => $date->format('y'),
                'firstName' => 'foo',
                'lastName' => 'bar',
            )
        ));

        $gateway->execute($capture);

        $statusRequest = new GetHumanStatus($capture->getModel());
        $gateway->execute($statusRequest);

        $this->assertTrue($statusRequest->isCaptured());
    }

    /**
     * @test
     */
    public function shouldFinishWithFailed()
    {
        $factory = new OmnipayGatewayFactory('Dummy');

        $gateway = $factory->create([]);

        $date = new \DateTime('now + 2 year');

        $capture = new Capture(array(
            'amount' => '1000.00',
            'card' => array(
                'number' => '4111111111111111', //must be declined,
                'cvv' => 123,
                'expiryMonth' => 6,
                'expiryYear' => $date->format('y'),
                'firstName' => 'foo',
                'lastName' => 'bar',
            )
        ));

        $gateway->execute($capture);

        $statusRequest = new GetHumanStatus($capture->getModel());
        $gateway->execute($statusRequest);

        $this->assertTrue($statusRequest->isFailed());
    }
}
