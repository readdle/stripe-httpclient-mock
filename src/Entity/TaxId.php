<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class TaxId extends AbstractEntity
{
    protected array $props = [
        'country'      => '',
        'created'      => 0,
        'customer'     => '',
        'livemode'     => false,
        'type'         => '',
        'value'        => '',
        'verification' => [
            'status'           => 'pending',
            'verified_address' => null,
            'verified_name'    => null,
        ]
    ];

    public static function prefix(): string
    {
        return 'txi';
    }
}
