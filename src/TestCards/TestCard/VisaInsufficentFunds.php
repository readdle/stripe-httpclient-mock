<?php

namespace Readdle\StripeHttpClientMock\TestCards\TestCard;


use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\Error\CardError;

/**
 * User: tarjei
 * Date: 14.05.2023 / 13:56
 */
class VisaInsufficentFunds extends AbstractTestCardErrorResult implements TestCardInterface
{
    public const CARD_NUMBER = "4000000000009995";
    public const PAYMENT_METHOD = "pm_card_visa_chargeDeclinedInsufficientFunds";
    protected string $errorCode = "card_declined";
    protected string $declineCode = "insufficient_funds";

    protected string $cardNumber = self::CARD_NUMBER;
    protected string $paymentMethod = self::PAYMENT_METHOD;
    protected string $errorMessage = "Your card was declined.";

}
