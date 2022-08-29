<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Error;

use Readdle\StripeHttpClientMock\ResponseInterface;

abstract class AbstractError implements ResponseInterface
{
    public array $error = [
        'code'    => '',
        'doc_url' => '',
        'message' => '',
        'param'   => '',
        'type'    => '',
    ];

    public function __construct(string $message = '')
    {
        $this->error['message'] = $message;
    }

    abstract public function getHttpStatusCode(): int;

    public function toArray(): array
    {
        return ['error' => array_filter($this->error)];
    }

    public function toString(): string
    {
        return json_encode($this->toArray());
    }
}
