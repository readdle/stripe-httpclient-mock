<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\Error\InvalidRequest;
use Readdle\StripeHttpClientMock\ResponseInterface;

class Price extends AbstractEntity
{
    protected array $props = [
        'active'              => true,
        'billing_scheme'      => 'per_unit',
        'created'             => null,
        'currency'            => null,
        'custom_unit_amount'  => null,
        'livemode'            => false,
        'lookup_key'          => null,
        'metadata'            => [],
        'nickname'            => null,
        'product'             => null,
        'recurring'           => null,
        'tax_behavior'        => 'unspecified',
        'tiers_mode'          => null,
        'transform_quantity'  => null,
        'type'                => 'one_time',
        'unit_amount'         => null,
        'unit_amount_decimal' => null,
        'currency_options'    => [],
    ];

    protected static array $expandableProps = [
        'product',
    ];

    public static function create(string $id, array $props = []): ResponseInterface
    {
        if (array_key_exists('unit_amount', $props) && !array_key_exists('unit_amount_decimal', $props)) {
            // pretty dumb stub, would be great to improve
            $props['unit_amount_decimal'] = (string) $props['unit_amount'];
        }

        return parent::create($id, $props);
    }

    public function update(array $props): ResponseInterface
    {
        if (array_key_exists('lookup_key', $props)) {
            $pricesByLookupKey = EntityManager::listEntity('price', ['lookup_key' => $props['lookup_key']]);

            if (!empty($pricesByLookupKey->data)) {
                return new InvalidRequest(
                    'A price (`' . $pricesByLookupKey->data[0]->id . '`) already uses that lookup key.',
                    'lookup_key'
                );
            }
        }

        return parent::update($props);
    }
}
