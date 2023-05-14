<?php

namespace Readdle\StripeHttpClientMock\TestCards\TestCard;


use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\Error\CardError;

/**
 * User: tarjei
 * Date: 14.05.2023 / 13:56
 */
class VisaChargeDeclined extends AbstractTestCardErrorResult implements TestCardInterface
{
    protected string $errorCode = "card_declined";
    protected string $declineCode = "generic_decline";

    protected string $cardNumber = "4000000000000002";
    protected string $paymentMethod = "pm_card_visa_chargeDeclined";

    protected string $errorMessage = "Your card was declined.";

}
