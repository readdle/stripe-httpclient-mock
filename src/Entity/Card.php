<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Card extends AbstractEntity
{
    protected array $props = [
        'address_city'        => null,
        'address_country'     => null,
        'address_line1'       => null,
        'address_line1_check' => null,
        'address_line2'       => null,
        'address_state'       => null,
        'address_zip'         => null,
        'address_zip_check'   => null,
        'brand'               => null,
        'country'             => null,
        'customer'            => null,
        'cvc_check'           => null,
        'dynamic_last4'       => null,
        'exp_month'           => null,
        'exp_year'            => null,
        'fingerprint'         => null,
        'funding'             => null,
        'last4'               => null,
        'metadata'            => [],
        'name'                => null,
        'tokenization_method' => null,
    ];
}
