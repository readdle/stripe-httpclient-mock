<?php
/**
 * User: tarjei
 * Date: 14.05.2023 / 13:49
 */

namespace Readdle\StripeHttpClientMock\TestCards\TestCard;

use Readdle\StripeHttpClientMock\Entity\PaymentIntent;
use Readdle\StripeHttpClientMock\ResponseInterface;

interface TestCardInterface
{

    public function getCardNumber(): string;

    public function getPaymentMethod(): string;

    public function createConfirmResult(PaymentIntent $intent): ResponseInterface;


}
