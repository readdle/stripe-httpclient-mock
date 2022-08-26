<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class PromotionCode extends AbstractEntity
{
    protected array $props = [
        'active'          => true,
        'code'            => null,
        'coupon'          => null,
        'created'         => null,
        'customer'        => [],
        'expires_at'      => null,
        'livemode'        => false,
        'max_redemptions' => null,
        'metadata'        => [],
        'restrictions'    => [
            'first_time_transaction'  => null,
            'minimum_amount'          => null,
            'minimum_amount_currency' => null
        ],
        'times_redeemed'  => 0,
    ];

    public static function create(string $id, array $props = []): AbstractEntity
    {
        /** @var PromotionCode $entity */
        $entity = parent::create($id, $props);
        $entity->props['code'] = uniqid();
        return $entity;
    }

    public static function prefix(): string
    {
        return 'promo';
    }
}
