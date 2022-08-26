<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Exception;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\ResponseInterface;

class LineItem extends AbstractEntity
{
    protected array $props = [
        'amount'                    => null,
        'amount_excluding_tax'      => null,
        'currency'                  => null,
        'description'               => null,
        'discount_amounts'          => [],
        'discountable'              => null,
        'discounts'                 => [],
        'invoice_item'              => null,
        'livemode'                  => false,
        'metadata'                  => [],
        'period'                    => [
            'end'   => null,
            'start' => null,
        ],
        'price'                     => null,
        'proration'                 => false,
        'proration_details'         => [
            'credited_items' => null
        ],
        'quantity'                  => null,
        'subscription'              => null,
        'tax_amounts'               => [],
        'tax_rates'                 => [],
        'type'                      => null,
        'unit_amount_excluding_tax' => null,
    ];

    public static function prefix(): string
    {
        return 'il';
    }

    public static function create(string $id, array $props = []): ResponseInterface
    {
        /** @var LineItem $entity */
        $entity = parent::create($id, $props);
        $entity->props['object'] = 'line_item';

        if (!empty($entity->props['price']) && is_string($entity->props['price'])) {
            $entity->props['price'] = EntityManager::retrieveEntity('price', $entity->props['price']);
        }

        if (!empty($entity->props['tax_rates'])) {
            $entity->props['tax_rates'] = array_map(
                fn ($taxRateId) => is_string($taxRateId)
                    ? EntityManager::retrieveEntity('tax_rate', $taxRateId)
                    : $taxRateId,
                $entity->props['tax_rates']
            );
        }

        return $entity;
    }

    /**
     * @throws Exception
     */
    public static function createFromInvoiceItem(InvoiceItem $invoiceItem): LineItem
    {
        /** @noinspection PhpUndefinedFieldInspection */
        /** @noinspection SpellCheckingInspection */
        /** @var LineItem */
        return EntityManager::createEntity('line_item', [
            'amount'       => $invoiceItem->amount,
            'currency'     => $invoiceItem->currency,
            'description'  => $invoiceItem->description,
            'discountable' => $invoiceItem->discountable,
            'discounts'    => $invoiceItem->discounts,
            'invoice_item' => $invoiceItem->id,
            'period'       => $invoiceItem->period,
            'price'        => $invoiceItem->price,
            'proration'    => $invoiceItem->proration,
            'quantity'     => $invoiceItem->quantity,
            'subscription' => $invoiceItem->subscription,
            'tax_rates'    => $invoiceItem->tax_rates,
            'type'         => 'invoiceitem',
        ]);
    }
}
