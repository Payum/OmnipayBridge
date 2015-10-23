<?php
namespace Payum\OmnipayBridge\Tests\Action;

use Omnipay\Common\Message\AbstractResponse as OmnipayAbstractResponse;
use Omnipay\Common\Message\RequestInterface as OmnipayRequestInterface;
use Omnipay\Common\Message\ResponseInterface as OmnipayResponseInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Tests\GenericActionTest;
use Payum\OmnipayBridge\Action\BaseApiAwareAction;
use Payum\OmnipayBridge\Action\OffsiteCaptureAction;
use Payum\OmnipayBridge\Tests\CreditCardGateway;
use Payum\OmnipayBridge\Tests\OffsiteGateway;

class OffsiteCaptureActionTest extends GenericActionTest
{
    protected $actionClass = OffsiteCaptureAction::class;

    protected $requestClass = Capture::class;

    /**
     * @var  OffsiteCaptureAction
     */
    protected $action;

    protected function setUp()
    {
        $this->action = new $this->actionClass();
        $this->action->setApi(new OffsiteGateway());
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfBaseApiAwareAction()
    {
        $rc = new \ReflectionClass(OffsiteCaptureAction::class);
        
        $this->assertTrue($rc->isSubclassOf(BaseApiAwareAction::class));
    }

    /**
     * @test
     */
    public function shouldImplementInterfaceGatewayAwareAction()
    {
        $rc = new \ReflectionClass(OffsiteCaptureAction::class);

        $this->assertTrue($rc->isSubclassOf(GatewayAwareInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementInterfaceGenericTokenFactoryAwareInterface()
    {
        $rc = new \ReflectionClass(OffsiteCaptureAction::class);

        $this->assertTrue($rc->implementsInterface(GenericTokenFactoryAwareInterface::class));
    }

    public function shouldNotSupportIfCreditCardOmnipayGatewaySetAsApi()
    {
        $this->action->setApi(new CreditCardGateway());

        $this->assertFalse($this->action->supports(new Capture([])));
        $this->assertFalse($this->action->supports(new Capture(new \ArrayObject())));
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage The bridge supports only responses which extends AbstractResponse. Their ResponseInterface is useless.
     */
    public function throwsIfPurchaseMethodReturnResponseNotInstanceOfAbstractResponse()
    {
        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($this->getMock(OmnipayResponseInterface::class)))
        ;

        $gateway = new OffsiteGateway();
        $gateway->returnOnPurchase = $requestMock;

        $action = new OffsiteCaptureAction;
        $action->setApi($gateway);
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Capture([]));
    }

    /**
     * @test
     */
    public function shouldCallGatewayPurchaseMethodWithExpectedArguments()
    {
        $details = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        );

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->once())
            ->method('purchase')
            ->with($details)
            ->willReturn($requestMock)
        ;
        $omnipayGateway
            ->expects($this->never())
            ->method('completePurchase')
        ;

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Capture($details));
    }

    /**
     * @test
     */
    public function shouldCallGatewayCompletePurchaseMethodWithExpectedArguments()
    {
        $details = array(
            '_completeCaptureRequired' => true,
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        );

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->once())
            ->method('completePurchase')
            ->with($details)
            ->willReturn($requestMock)
        ;
        $omnipayGateway
            ->expects($this->never())
            ->method('purchase')
        ;

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Capture($details));
    }

    /**
     * @test
     */
    public function shouldNotCallGatewayCompletePurchaseMethodIfAlreadyCompleted()
    {
        $details = array(
            '_completeCaptureRequired' => true,
            '_captureCompleted' => true,
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        );

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->never())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->never())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->never())
            ->method('completePurchase')
            ->with($details)
            ->willReturn($requestMock)
        ;
        $omnipayGateway
            ->expects($this->never())
            ->method('purchase')
        ;

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Capture($details));
    }

    /**
     * @test
     */
    public function shouldDoNothingIfStatusAlreadySet()
    {
        $details = array(
            '_status' => 'foo',
        );

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->never())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->never())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->never())
            ->method('completePurchase')
            ->with($details)
            ->willReturn($requestMock)
        ;
        $omnipayGateway
            ->expects($this->never())
            ->method('purchase')
        ;

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());

        $action->execute(new Capture($details));
    }

    /**
     * @test
     */
    public function shouldSetCaptureTokenTargetUrlAsReturnUrl()
    {
        $details = new \ArrayObject([
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        ]);

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->once())
            ->method('purchase')
            ->willReturn($requestMock)
        ;

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());

        $token = new Token();
        $token->setTargetUrl('theCaptureUrl');

        $request = new Capture($token);
        $request->setModel($details);

        $action->execute($request);

        $details = (array) $details;
        $this->assertArrayHasKey('returnUrl', $details);
        $this->assertEquals('theCaptureUrl', $details['returnUrl']);
    }

    /**
     * @test
     */
    public function shouldSetCaptureTokenTargetUrlAsCancelUrl()
    {
        $details = new \ArrayObject([
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        ]);

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->once())
            ->method('purchase')
            ->willReturn($requestMock)
        ;

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());

        $token = new Token();
        $token->setTargetUrl('theCaptureUrl');

        $request = new Capture($token);
        $request->setModel($details);

        $action->execute($request);

        $details = (array) $details;
        $this->assertArrayHasKey('cancelUrl', $details);
        $this->assertEquals('theCaptureUrl', $details['cancelUrl']);
    }

    /**
     * @test
     */
    public function shouldSetNotifyUrlIfTokenFactoryAndCaptureTokenPresent()
    {
        $details = new \ArrayObject([
            'foo' => 'fooVal',
            'bar' => 'barVal',
            'card' => array('cvv' => 123),
            'clientIp' => '',
        ]);

        $responseMock = $this->getMock(OmnipayAbstractResponse::class, [], [], '', false);
        $responseMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn([])
        ;

        $requestMock = $this->getMock(OmnipayRequestInterface::class);
        $requestMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnValue($responseMock))
        ;

        $omnipayGateway = $this->getMock(OffsiteGateway::class);
        $omnipayGateway
            ->expects($this->once())
            ->method('purchase')
            ->willReturn($requestMock)
        ;

        $captureToken = new Token();
        $captureToken->setTargetUrl('theCaptureUrl');
        $captureToken->setDetails($identity = new Identity('theId', new \stdClass()));
        $captureToken->setGatewayName('theGatewayName');

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactoryMock = $this->getMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createNotifyToken')
            ->with('theGatewayName', $this->identicalTo($identity))
            ->willReturn($notifyToken)
        ;


        $request = new Capture($captureToken);
        $request->setModel($details);

        $action = new OffsiteCaptureAction;
        $action->setApi($omnipayGateway);
        $action->setGateway($this->createGatewayMock());
        $action->setGenericTokenFactory($tokenFactoryMock);

        $action->execute($request);

        $details = (array) $details;
        $this->assertArrayHasKey('notifyUrl', $details);
        $this->assertEquals('theNotifyUrl', $details['notifyUrl']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMock(GatewayInterface::class);
    }
}
