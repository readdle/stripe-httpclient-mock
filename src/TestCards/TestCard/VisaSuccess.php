<?php

namespace Readdle\StripeHttpClientMock\TestCards\TestCard;


use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\ResponseInterface;

/**
 * User: tarjei
 * Date: 14.05.2023 / 13:56
 */
class VisaSuccess implements TestCardInterface
{
    public const CARD_NUMBER    = "4242424242424242";
    public const PAYMENT_METHOD = "pm_card_visa";

    protected string $cardNumber    = self::CARD_NUMBER;
    protected string $paymentMethod = self::PAYMENT_METHOD;


    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function createConfirmResult(PaymentIntent $intent): ResponseInterface
    {
        $intent->status = "succeeded";
        $intent->amount_capturable = $intent->amount;
        $intent->amount_received = $intent->amount;
        $intent->last_payment_error = null;
        $charge = EntityManager::createEntity('charge', [
            'amount' => $intent->amount,
            "amount_captured" => $intent->amount,
            "amount_refunded" => 0,
        ]);
        $intent->latest_charge = $charge->id;
        return $intent;
    }

}
