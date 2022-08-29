<?php

namespace Readdle\StripeHttpClientMock\Error;

class InvalidRequest extends AbstractError
{
    public function __construct(string $message, string $param = '')
    {
        parent::__construct($message);

        $this->error['type'] = 'invalid_request_error';
        $this->error['param'] = $param;
    }

    public function getHttpStatusCode(): int
    {
        return 400;
    }
}
