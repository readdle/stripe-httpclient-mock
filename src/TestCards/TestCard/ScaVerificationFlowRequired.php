<?php

namespace Readdle\StripeHttpClientMock\TestCards\TestCard;


use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\ResponseInterface;

/**
 * This card implements the SCA verification flow for a payment intent.
 * I.e. when you try to confirm it, it will return a requires_action response.
 *
 *
 * User: tarjei
 * Date: 14.05.2023 / 16:32
 */
class ScaVerificationFlowRequired implements TestCardInterface
{

    public const CARD_NUMBER = "4000002760003184";

    public const PAYMENT_METHOD = "pm_card_threeDSecureRequired";
    public function getCardNumber(): string
    {
        return self::CARD_NUMBER;
    }

    public function getPaymentMethod(): string
    {
        return self::PAYMENT_METHOD;
    }

    public function createConfirmResult(PaymentIntent $intent): ResponseInterface
    {

        $intent->update([
            "status" => "requires_action",
            'client_secret' => 'src_client_secret_' . uniqid(),
            "next_action" => [
                "type" => "use_stripe_sdk",
                "use_stripe_sdk" => [
                    "type" => "three_d_secure_redirect",
                    "stripe_js" => "https://hooks.stripe-mock.com/redirect/authenticate/src_ddd?client_secret=src_client_secret_GxJ5a2eZvKYlo2CJ9jQYQ4X",
                    "source" => "src_1GxJ5a2eZvKYlo2CJ9jQYQ4X",
                ],
            ],
        ]);
        return $intent;
    }

}
