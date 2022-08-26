<?php

namespace Readdle\StripeHttpClientMock\Entity;

class TaxRate extends AbstractEntity
{
    protected array $props = [
        'active'       => true,
        'country'      => null,
        'created'      => null,
        'description'  => null,
        'display_name' => null,
        'inclusive'    => false,
        'jurisdiction' => null,
        'livemode'     => false,
        'metadata'     => [],
        'percentage'   => null,
        'state'        => null,
        'tax_type'     => null,
    ];

    public static function prefix(): string
    {
        return 'txr';
    }
}
