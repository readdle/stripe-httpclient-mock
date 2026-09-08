<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\EntityManager;

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

        if (empty($props['code'])) {
            $entity->props['code'] = uniqid();
        }

        return $entity;
    }

    public static function prefix(): string
    {
        return 'promo';
    }

    /**
     * Stripe API "Basil" moved the coupon under `promotion.coupon` (kept as an internal `coupon`
     * prop above so existing `coupon => $id` list filters still work).
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $couponId = $array['coupon'] ?? null;
        unset($array['coupon']);

        $couponData = null;
        if (!empty($couponId)) {
            $couponEntity = EntityManager::retrieveEntity('coupon', $couponId);
            $couponData = $couponEntity instanceof AbstractEntity ? $couponEntity->toArray() : $couponId;
        }

        $array['promotion'] = [
            'type'   => 'coupon',
            'coupon' => $couponData,
        ];

        return $array;
    }
}
