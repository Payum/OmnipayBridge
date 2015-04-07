<?php
namespace Payum\OmnipayBridge\Tests\Action\Api;

use Payum\Core\Model\CreditCard;
use Payum\OmnipayBridge\Action\FillOrderDetailsAction;
use Payum\Core\Model\Payment;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Tests\GenericActionTest;

class FillOrderDetailsActionTest extends GenericActionTest
{
    protected $actionClass = 'Payum\OmnipayBridge\Action\FillOrderDetailsAction';

    protected $requestClass = 'Payum\Core\Request\FillOrderDetails';

    public function provideSupportedRequests()
    {
        return array(
            array(new $this->requestClass(new Payment)),
            array(new $this->requestClass($this->getMock('Payum\Core\Model\OrderInterface'))),
            array(new $this->requestClass(new Payment, $this->getMock('Payum\Core\Security\TokenInterface'))),
        );
    }

    public function provideNotSupportedRequests()
    {
        return array(
            array('foo'),
            array(array('foo')),
            array(new \stdClass()),
            array($this->getMockForAbstractClass('Payum\Core\Request\Generic', array(array()))),
        );
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack()
    {
        $payment = new Payment;
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        $action = new FillOrderDetailsAction;

        $action->execute(new FillOrderDetails($payment));

        $details = $payment->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('amount', $details);
        $this->assertEquals(1.23, $details['amount']);

        $this->assertArrayHasKey('currency', $details);
        $this->assertEquals('USD', $details['currency']);

        $this->assertArrayHasKey('description', $details);
        $this->assertEquals('the description', $details['description']);
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderCreditCardToDetailsAndSetItBack()
    {
        $creditCard = new CreditCard();
        $creditCard->setNumber('4444333322221111');
        $creditCard->setHolder('John Doe');
        $creditCard->setSecurityCode('322');
        $creditCard->setExpireAt(new \DateTime('2015-11-12'));

        $payment = new Payment;
        $payment->setNumber('theNumber');
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');
        $payment->setCreditCard($creditCard);

        $action = new FillOrderDetailsAction;

        $action->execute(new FillOrderDetails($payment));

        $details = $payment->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('card', $details);
        $this->assertInstanceOf('Payum\Core\Security\SensitiveValue', $details['card']);
        $this->assertEquals(array(
            'number' => '4444333322221111',
            'cvv' => '322',
            'expiryMonth' => '11',
            'expiryYear' => '15',
            'firstName' => 'John Doe',
            'lastName' => '',
        ), $details['card']->peek());
    }

    /**
     * @test
     */
    public function shouldNotOverwriteAlreadySetExtraDetails()
    {
        $payment = new Payment;
        $payment->setCurrencyCode('USD');
        $payment->setTotalAmount(123);
        $payment->setDescription('the description');
        $payment->setDetails(array(
            'foo' => 'fooVal',
        ));

        $action = new FillOrderDetailsAction;

        $action->execute(new FillOrderDetails($payment));

        $details = $payment->getDetails();

        $this->assertNotEmpty($details);

        $this->assertArrayHasKey('foo', $details);
        $this->assertEquals('fooVal', $details['foo']);
    }
}