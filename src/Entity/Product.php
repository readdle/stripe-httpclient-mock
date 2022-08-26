<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Product extends AbstractEntity
{
    protected array $props = [
        'active'               => true,
        'created'              => null,
        'default_price'        => null,
        'description'          => null,
        'images'               => [],
        'livemode'             => false,
        'metadata'             => [],
        'name'                 => null,
        'package_dimensions'   => null,
        'shippable'            => null,
        'statement_descriptor' => null,
        'tax_code'             => null,
        'unit_label'           => null,
        'updated'              => 0,
        'url'                  => null,
    ];
}
