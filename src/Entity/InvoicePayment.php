<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class InvoicePayment extends AbstractEntity
{
    protected array $props = [
        'amount_paid'        => null,
        'amount_requested'   => null,
        'created'            => null,
        'currency'           => null,
        'invoice'            => null,
        'is_default'         => true,
        'livemode'           => false,
        'payment'            => [
            'type' => null,
        ],
        'status'             => 'paid',
        'status_transitions' => [
            'canceled_at' => null,
            'paid_at'     => null,
        ],
    ];

    public static function prefix(): string
    {
        return 'ip';
    }

    public static function objectName(): string
    {
        return 'invoice_payment';
    }
}
