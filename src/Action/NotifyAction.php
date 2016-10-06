<?php

namespace Payum\OmnipayBridge\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;

/**
 * @author Steffen Brem <steffenbrem@gmail.com>
 */
class NotifyAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $response = $this->omnipayGateway->fetchTransaction($details->toUnsafeArray())->send();

        $data = $response->getData();

        if (is_array($data)) {
            $details->replace($data);
        }

        $details['_reference'] = $response->getTransactionReference();
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
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
