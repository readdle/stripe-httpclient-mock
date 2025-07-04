<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Exception;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\Error\ResourceMissing;
use Readdle\StripeHttpClientMock\ResponseInterface;

class InvoiceItem extends AbstractEntity
{
    protected array $props = [
        'amount'              => null,
        'currency'            => null,
        'customer'            => null,
        'date'                => null,
        'description'         => null,
        'discountable'        => null,
        'discounts'           => [],
        'invoice'             => null,
        'livemode'            => false,
        'metadata'            => [],
        'period'              => [
            'start' => null,
            'end'   => null,
        ],
        'price'               => null,
        'proration'           => false,
        'quantity'            => null,
        'subscription'        => null,
        'tax_rates'           => [],
        'test_clock'          => null,
        'unit_amount'         => null,
        'unit_amount_decimal' => null
    ];

    public static function prefix(): string
    {
        return 'ii';
    }

    public static function objectName(): string
    {
        /** @noinspection SpellCheckingInspection */
        return 'invoiceitem';
    }

    /**
     * @throws Exception
     */
    public static function create(string $id, array $props = []): ResponseInterface
    {
        if (array_key_exists('invoice', $props)) {
            /** @var Invoice $invoice */
            $invoice = EntityManager::retrieveEntity('invoice', $props['invoice']);

            if ($invoice instanceof ResourceMissing) {
                return new ResourceMissing("No such invoice: '{$props['invoice']}'", 'invoice');
            }
        }

        /** @var InvoiceItem $entity */
        $entity = parent::create($id, $props);

        if (!empty($invoice)) {
            $lines = $invoice->lines;
            $lines['data'][] = LineItem::createFromInvoiceItem($entity);
            $invoice->update(['lines' => $lines]);
        }

        return $entity;
    }
}
