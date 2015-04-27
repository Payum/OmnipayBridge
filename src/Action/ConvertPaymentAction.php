<?php
namespace Payum\OmnipayBridge\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Security\SensitiveValue;

class ConvertPaymentAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $divisor = pow(10, $currency->exp);

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

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            'array' == $request->getTo() &&
            $request->getSource() instanceof PaymentInterface
        ;
    }
}
