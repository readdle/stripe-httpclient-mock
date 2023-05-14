<?php

namespace Readdle\StripeHttpClientMock\Error;


use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\TestCards\TestCard\TestCardInterface;

/**
 * User: tarjei
 * Date: 14.05.2023 / 14:51
 */
class CardError extends AbstractError
{

    private TestCardInterface $card;

    public function __construct(TestCardInterface $card, PaymentIntent $intent)
    {
        parent::__construct();
        $this->card = $card;
        $this->error['paymentIntent'] = $intent->toArray();
        $this->error['code'] = $card->getErrorCode();
        $this->error['charge'] = 'ch_not_implemented'; // todo: can / should we create a charge as well?
        $this->error["decline_code"] = $card->getDeclineCode();
        $this->error["doc_url"] = "https://stripe.com/docs/error-codes/card-declined";
        $this->error["message"] = $card->getMessage();
    }

    public function getHttpStatusCode(): int
    {
        return $this->card->getResponseCode();
    }
}
