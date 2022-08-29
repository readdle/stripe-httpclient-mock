<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\ResponseInterface;

class SubscriptionItem extends AbstractEntity
{
    protected array $props = [
        'billing_thresholds' => null,
        'created'            => null,
        'metadata'           => [],
        'plan'               => null,
        'price'              => [],
        'quantity'           => 0,
        'subscription'       => null,
        'tax_rates'          => [],
    ];

    public static function create(string $id, array $props = []): ResponseInterface
    {
        if (array_key_exists('price', $props)) {
            $props['price'] = EntityManager::retrieveEntity('price', $props['price']);
        }

        return parent::create($id, $props);
    }
}
