<?php

namespace Readdle\StripeHttpClientMock\TestCards\TestCard;


use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\Error\CardError;

/**
 * User: tarjei
 * Date: 14.05.2023 / 14:58
 */
class AbstractTestCardErrorResult
{

    protected string $errorCode;
    protected string $declineCode;

    /**
     * @return string
     */
    public function getDeclineCode(): string
    {
        return $this->declineCode;
    }

    protected string $cardNumber;
    protected string $paymentMethod;

    protected string $errorMessage;
    private int      $responsCode = 402;

    public function createConfirmResult(PaymentIntent $intent): CardError
    {
        $intent->status = "requires_payment_method";
        $intent->amount_capturable = 0;
        $intent->amount_received = 0;
        $intent->last_payment_error = [
            "code" => $this->errorCode,
            "decline_code" => $this->declineCode,
            "doc_url" => "https://stripe.com/docs/error-codes/card-declined",
            "message" => $this->errorMessage,
            "payment_method" => $intent->payment_method,
        ];
        $response = new CardError($this, $intent);

        return $response;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getResponseCode(): int
    {
        return $this->responsCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getMessage(): string
    {
        return $this->errorMessage;
    }

}

