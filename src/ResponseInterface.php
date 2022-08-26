<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock;

interface ResponseInterface
{
    public function toString(): string;
}
