<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class SubscriptionItem extends AbstractEntity
{
    protected array $props = [
        'billing_thresholds' => null,
        'created'            => null,
        'metadata'           => [],
        'price'              => [],
        'quantity'           => 0,
        'subscription'       => null,
        'tax_rates'          => [],
    ];
}
