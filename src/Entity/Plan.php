<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Plan extends AbstractEntity
{
    protected array $props = [
        'active'              => true,
        'amount'              => 0,
        'object'              => 'plan',
        'billing_scheme'      => 'per_unit',
        'created'             => null,
        'currency'            => 'usd',
        "interval"            => "month",
        "interval_count"      => 1,
        'livemode'            => false,
        'metadata'            => [],
        'nickname'            => null,
        'product'             => null,
        'tiers_mode'          => null,
        'trial_period_days'  => null,
        'usage_type'         => "licensed",
    ];

    protected static array $expandableProps = [
        'product',
    ];


}
