<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

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
}
