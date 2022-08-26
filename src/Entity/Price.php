<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Price extends AbstractEntity
{
    protected array $props = [
        'active'             => true,
        'billing_scheme'     => 'per_unit',
        'created'            => null,
        'currency'           => null,
        'custom_unit_amount' => null,
        'livemode'           => false,
        'lookup_key'         => null,
        'metadata'           => [],
        'nickname'           => null,
        'product'            => null,
        'recurring'          => [
            'aggregate_usage'   => null,
            'interval'          => '',
            'interval_count'    => 0,
            'trial_period_days' => null,
            'usage_type'        => 'licensed',
        ],
        'tax_behavior'        => 'unspecified',
        'tiers_mode'          => null,
        'transform_quantity'  => null,
        'type'                => 'recurring',
        'unit_amount'         => null,
        'unit_amount_decimal' => null,
    ];
}
