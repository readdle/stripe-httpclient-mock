<?php

namespace Readdle\StripeHttpClientMock\TestCards;


use Readdle\StripeHttpClientMock\TestCards\TestCard\ScaVerificationFlowRequired;
use Readdle\StripeHttpClientMock\TestCards\TestCard\TestCardInterface;
use Readdle\StripeHttpClientMock\TestCards\TestCard\VisaChargeDeclined;

/**
 * User: tarjei
 * Date: 14.05.2023 / 13:49
 */
class TestCardRegistry
{

    private $cards = [];

    public function __construct()
    {
        $this->cards = [
            new VisaChargeDeclined(),
            new ScaVerificationFlowRequired()
        ];
    }

    public function getCard(?string $cardNumber, ?string $paymentMethod): ?TestCardInterface
    {
        foreach ($this->cards as $card) {
            if ($card->getCardNumber() === $cardNumber || $card->getPaymentMethod() === $paymentMethod) {
                return $card;
            }
        }

        return null;
    }


}
