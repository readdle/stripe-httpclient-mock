<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Customer extends AbstractEntity
{
    protected array $props = [
        'address'               => null,
        'balance'               => 0,
        'created'               => null,
        'currency'              => null,
        'default_source'        => null,
        'delinquent'            => false,
        'description'           => null,
        'discount'              => null,
        'email'                 => null,
        'invoice_prefix'        => null,
        'invoice_settings'      => [
            'custom_fields'          => null,
            'default_payment_method' => null,
            'footer'                 => null,
            'rendering_options'      => null,
        ],
        'livemode'              => false,
        'metadata'              => [],
        'name'                  => null,
        'next_invoice_sequence' => null,
        'phone'                 => null,
        'preferred_locales'     => [],
        'shipping'              => null,
        'tax_exempt'            => 'none',
        'test_clock'            => null,
    ];

    protected static array $subEntities = ['tax_ids'];

    public static function prefix(): string
    {
        return 'cus';
    }
}
