<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\SensitiveValue;
use Payum\Offline\Constants;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getOrder();
        $divisor = pow(10, $payment->getCurrencyDigitsAfterDecimalPoint());

        $details = $payment->getDetails();
        $details['amount'] = (float) $payment->getTotalAmount() / $divisor;
        $details['currency'] = $payment->getCurrencyCode();
        $details['description'] = $payment->getDescription();

        if ($payment->getCreditCard()) {
            $card = $payment->getCreditCard();

            $details['card'] = new SensitiveValue(array(
                'number' => $card->getNumber(),
                'cvv' => $card->getSecurityCode(),
                'expiryMonth' => $card->getExpireAt()->format('m'),
                'expiryYear' => $card->getExpireAt()->format('y'),
                'firstName' => $card->getHolder(),
                'lastName' => '',
            ));
        }

        $payment->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
