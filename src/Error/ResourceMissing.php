<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Error;

class ResourceMissing extends AbstractError
{
    public function __construct(string $message = '', string $param = '')
    {
        parent::__construct($message);
        $this->error['code'] = 'resource_missing';
        $this->error['type'] = 'invalid_request_error';
        $this->error['param'] = $param;
        $this->error['doc_url'] = 'https://stripe.com/docs/error-codes/resource-missing';
        if (\Stripe\Stripe::getLogger()) {
            \Stripe\Stripe::getLogger()->error('[stripe-client-mock]: ' . $message . " " . $param);
        }
    }

    public function getHttpStatusCode(): int
    {
        return 400;
    }
}
