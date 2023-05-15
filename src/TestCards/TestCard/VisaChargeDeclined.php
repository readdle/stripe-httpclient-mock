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
    public const CARD_NUMBER = "4000000000000002";
    public const PAYMENT_METHOD = "pm_card_visa_chargeDeclined";
l

    protected string $errorCode = "card_declined";
    protected string $declineCode = "generic_decline";

    protected string $errorMessage = "Your card was declined.";

}
