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
    protected string $errorCode = "card_declined";
    protected string $declineCode = "insufficient_funds";

    protected string $cardNumber = "4000000000009995";
    protected string $paymentMethod = "pm_card_visa_chargeDeclinedInsufficientFunds";

    protected string $errorMessage = "Your card was declined.";

}
