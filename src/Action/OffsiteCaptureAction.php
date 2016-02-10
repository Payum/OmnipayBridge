<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class OffsiteCaptureAction extends BaseApiAwareAction implements GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * {@inheritDoc}
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param GenericTokenFactoryInterface $genericTokenFactory
     *
     * @return void
     */
    public function setGenericTokenFactory(GenericTokenFactoryInterface $genericTokenFactory = null)
    {
        $this->tokenFactory = $genericTokenFactory;
    }


    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['_status'] || $details['_captureCompleted']) {
            return;
        }

        if (false == $details['returnUrl'] && $request->getToken()) {
            $details['returnUrl'] = $request->getToken()->getTargetUrl();
        }

        if (false == $details['cancelUrl'] && $request->getToken()) {
            $details['cancelUrl'] = $request->getToken()->getTargetUrl();
        }

        if (empty($details['notifyUrl']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );

            $details['notifyUrl'] = $notifyToken->getTargetUrl();
        }

        if (false == $details['clientIp']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest);

            $details['clientIp'] = $httpRequest->clientIp;
        }

        if (
            $details['_completeCaptureRequired'] &&
            method_exists($this->omnipayGateway, 'completePurchase')
        ) {
            if (false == $details['_captureCompleted']) {
                $response = $this->omnipayGateway->completePurchase($details->toUnsafeArray())->send();

                $details['_captureCompleted'] = true;
            }
        } else {
            $response = $this->omnipayGateway->purchase($details->toUnsafeArray())->send();

            $details['transactionReference'] = $response->getTransactionReference();
        }

        /** @var \Omnipay\Common\Message\AbstractResponse $response */
        if (false == $response instanceof \Omnipay\Common\Message\AbstractResponse) {
            throw new \LogicException('The bridge supports only responses which extends AbstractResponse. Their ResponseInterface is useless.');
        }

        if ($response->isRedirect()) {
            /** @var \Omnipay\Common\Message\AbstractResponse|\Omnipay\Common\Message\RedirectResponseInterface $response */
            if (false == $response instanceof \Omnipay\Common\Message\RedirectResponseInterface) {
                throw new \LogicException('The omnipay\'s tells its response is redirect but the response instance is not RedirectResponseInterface.');
            }

            $details['_completeCaptureRequired'] = 1;

            if ($response->getRedirectMethod() == 'POST') {
                throw new HttpPostRedirect($response->getRedirectUrl(), $response->getRedirectData());
            } else {
                throw new HttpRedirect($response->getRedirectUrl());
            }
        }

        $details->replace($response->getData());
        $details['_reference']      = $response->getTransactionReference();
        $details['_status']         = $response->isSuccessful() ? 'captured' : 'failed';
        $details['_status_code']    = $response->getCode();
        $details['_status_message'] = $response->isSuccessful() ? '' : $response->getMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess &&
            method_exists($this->omnipayGateway, 'purchase') &&
            method_exists($this->omnipayGateway, 'completePurchase')
        ;
    }
}
