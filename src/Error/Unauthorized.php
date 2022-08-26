<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Error;

class Unauthorized extends AbstractError
{
    public function __construct(string $apiKey)
    {
        parent::__construct('Invalid API Key provided: ' . $apiKey);
        $this->error['type'] = 'invalid_request_error';
    }

    public function getHttpStatusCode(): int
    {
        return 401;
    }
}
