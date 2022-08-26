<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Success;

use Readdle\StripeHttpClientMock\ResponseInterface;

class Deleted implements ResponseInterface
{
    public string $id;
    public string $object;
    public bool $deleted = true;

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toString(): string
    {
        return json_encode($this->toArray());
    }
}
