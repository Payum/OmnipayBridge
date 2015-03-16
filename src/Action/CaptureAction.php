<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\PaymentAwareInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Security\SensitiveValue;

class CaptureAction extends OffsiteCaptureAction implements PaymentAwareInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        if ($details['_status']) {
            return;
        }

        if (false == isset($details['_completeCaptureRequired'])) {
            if (false == $details->validateNotEmpty(array('card'), false) && false == $details->validateNotEmpty(array('cardReference'), false)) {
                try {
                    $this->payment->execute($creditCardRequest = new ObtainCreditCard);
                    $card = $creditCardRequest->obtain();

                    $details['card'] = new SensitiveValue(array(
                        'number' => $card->getNumber(),
                        'cvv' => $card->getSecurityCode(),
                        'expiryMonth' => $card->getExpireAt()->format('m'),
                        'expiryYear' => $card->getExpireAt()->format('y'),
                        'firstName' => $card->getHolder(),
                        'lastName' => '',
                    ));
                } catch (RequestNotSupportedException $e) {
                    throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
                }
            }
        }

        parent::execute($request);
    }
}
