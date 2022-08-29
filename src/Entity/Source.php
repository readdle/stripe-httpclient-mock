<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Source extends AbstractEntity
{
    protected array $props = [
        'ach_credit_transfer'  => null,
        'amount'               => null,
        'client_secret'        => null,
        'created'              => null,
        'currency'             => null,
        'flow'                 => null,
        'livemode'             => false,
        'metadata'             => [],
        'owner'                => null,
        'receiver'             => null,
        'statement_descriptor' => null,
        'status'               => null,
        'type'                 => null,
        'usage'                => null,
    ];
}
