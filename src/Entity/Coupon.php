<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Coupon extends AbstractEntity
{
    protected array $props = [
        'amount_off'         => null,
        'created'            => null,
        'currency'           => null,
        'duration'           => null,
        'duration_in_months' => null,
        'livemode'           => false,
        'max_redemptions'    => null,
        'metadata'           => [],
        'name'               => null,
        'percent_off'        => null,
        'redeem_by'          => null,
        'times_redeemed'     => 0,
        'valid'              => true,
    ];

    public static function prefix(): string
    {
        return '';
    }
}
