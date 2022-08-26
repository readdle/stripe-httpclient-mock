<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Error;

class ResourceMissing extends AbstractError
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);

        $this->error['code'] = 'resource_missing';
        $this->error['type'] = 'invalid_request_error';
    }

    public function getHttpStatusCode(): int
    {
        return 400;
    }
}
