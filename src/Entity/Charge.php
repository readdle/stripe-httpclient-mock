<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Charge extends AbstractEntity
{
    protected array $props = [
        'amount'                          => null,
        'amount_captured'                 => null,
        'amount_refunded'                 => null,
        'application'                     => null,
        'application_fee'                 => null,
        'application_fee_amount'          => null,
        'balance_transaction'             => null,
        'billing_details'                 => [],
        'calculated_statement_descriptor' => null,
        'captured'                        => false,
        'created'                         => null,
        'currency'                        => null,
        'customer'                        => null,
        'description'                     => null,
        'disputed'                        => false,
        'failure_balance_transaction'     => null,
        'failure_code'                    => null,
        'failure_message'                 => null,
        'fraud_details'                   => [],
        'invoice'                         => null,
        'livemode'                        => false,
        'metadata'                        => [],
        'on_behalf_of'                    => null,
        'outcome'                         => [],
        'paid'                            => false,
        'payment_intent'                  => null,
        'payment_method'                  => null,
        'payment_method_details'          => [],
        'receipt_email'                   => null,
        'receipt_number'                  => null,
        'receipt_url'                     => null,
        'refunded'                        => false,
        'refunds'                         => [],
        'review'                          => null,
        'shipping'                        => null,
        'source_transfer'                 => null,
        'statement_descriptor'            => null,
        'statement_descriptor_suffix'     => null,
        'status'                          => null,
        'transfer_data'                   => null,
        'transfer_group'                  => null,
    ];

    public static function prefix(): string
    {
        return 'ch';
    }
}
