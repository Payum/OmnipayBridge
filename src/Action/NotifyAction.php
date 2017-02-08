<?php

namespace Payum\OmnipayBridge\Action;

use Omnipay\Common\GatewayInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class NotifyAction implements ApiAwareInterface, ActionInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = GatewayInterface::class;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (method_exists($this->api, 'completePurchase')) {
            $response = $this->api->completePurchase($details->toUnsafeArray())->send();
        } else if (method_exists($this->api, 'acceptNotification')) {
            $response = $this->api->acceptNotification($details->toUnsafeArray())->send();
        }

        $details->replace((array)$response->getData());
        // Did you plan manage Cancelled status ?
        $details['_status'] = $response->isSuccessful() ? 'captured' : 'failed';
        $details['_status_code'] = $response->getCode();
        $details['_status_message'] = $response->isSuccessful() ? '' : $response->getMessage();

        throw new HttpResponse('OK', 200);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess && (
                method_exists($this->api, 'completePurchase') ||
                method_exists($this->api, 'acceptNotification')
            )
        ;
    }
}
