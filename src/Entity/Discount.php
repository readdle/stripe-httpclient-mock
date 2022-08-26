<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\ResponseInterface;

class Discount extends AbstractEntity
{
    protected array $props = [
        'checkout_session' => null,
        'coupon'           => [],
        'customer'         => null,
        'end'              => null,
        'invoice'          => null,
        'invoice_item'     => null,
        'promotion_code'   => null,
        'start'            => null,
        'subscription'     => null,
    ];

    public static function create(string $id, array $props = []): ResponseInterface
    {
        if (array_key_exists('coupon', $props) && is_string($props['coupon'])) {
            $props['coupon'] = EntityManager::retrieveEntity('coupon', $props['coupon'])->toArray();
        }

        return parent::create($id, $props);
    }
}
