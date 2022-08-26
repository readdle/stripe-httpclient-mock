<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Mandate extends AbstractEntity
{
    protected array $props = [
        'customer_acceptance'    => [],
        'livemode'               => false,
        'multi_use'              => [],
        'payment_method'         => null,
        'payment_method_details' => [],
        'status'                 => null,
        'type'                   => null,
    ];
}
