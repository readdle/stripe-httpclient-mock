<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class BalanceTransaction extends AbstractEntity
{
    protected array $props = [
        'amount' => 0, //  * @property int Gross amount of the transaction, in %s.
        'available_on' => 0, //  * @property int The date the transaction's net funds will become available in the Stripe balance.
        'created' => 0, //  * @property int Time at which the object was created. Measured in seconds since the Unix epoch.
        'currency' => 'usd', //  * @property string Three-letter <a href="https://www.iso.org/iso-4217-currency-codes.html">ISO currency code</a>, in lowercase. Must be a <a href="https://stripe.com/docs/currencies">supported currency</a>.
        'description' => 'Charge for xx' , //  * @property null|string An arbitrary string attached to the object. Often useful for displaying to users.
        'exchange_rate' => 0, //  * @property null|float The exchange rate used, if applicable, for this transaction. Specifically, if money was converted from currency A to currency B, then the <code>amount</code> in currency A, times <code>exchange_rate</code>, would be the <code>amount</code> in currency B. For example, suppose you charged a customer 10.00 EUR. Then the PaymentIntent's <code>amount</code> would be <code>1000</code> and <code>currency</code> would be <code>eur</code>. Suppose this was converted into 12.34 USD in your Stripe account. Then the BalanceTransaction's <code>amount</code> would be <code>1234</code>, <code>currency</code> would be <code>usd</code>, and <code>exchange_rate</code> would be <code>1.234</code>.
        'fee' => 42,  //  * @property int Fees (in %s) paid for this transaction.
        'fee_details' => [
              "amount" => 42,
              "application" => null,
              "currency" => "usd",
              "description" => "Stripe processing fees",
              "type" => "stripe_fee"
         ], //  * @property \Stripe\StripeObject[] Detailed breakdown of fees (in %s) paid for this transaction.
        'net' => 0, //  * @property int Net amount of the transaction, in %s.
        'reporting_category' => 'charge', //  * @property string <a href="https://stripe.com/docs/reports/reporting-categories">Learn more</a> about how reporting categories can help you understand balance transactions from an accounting perspective.
        'source' => 'ch_xx', //  * @property null|string|\Stripe\StripeObject The Stripe object to which this transaction is related.
        'status' => \Stripe\BalanceTransaction::TYPE_CHARGE, //  * @property string If the transaction's net funds are available in the Stripe balance yet. Either <code>available</code> or <code>pending</code>.
        'type' => 'charge', //  * @property string Transaction type: <code>adjustment</code>, <code>advance</code>, <code>advance_funding</code>, <code>anticipation_repayment</code>, <code>application_fee</code>, <code>application_fee_refund</code>, <code>charge</code>, <code>connect_collection_transfer</code>, <code>contribution</code>, <code>issuing_authorization_hold</code>, <code>issuing_authorization_release</code>, <code>issuing_dispute</code>, <code>issuing_transaction</code>, <code>payment</code>, <code>payment_failure_refund</code>, <code>payment_refund</code>, <code>payout</code>, <code>payout_cancel</code>, <code>payout_failure</code>, <code>refund</code>, <code>refund_failure</code>, <code>reserve_transaction</code>, <code>reserved_funds</code>, <code>stripe_fee</code>, <code>stripe_fx_fee</code>, <code>tax_fee</code>, <code>topup</code>, <code>topup_reversal</code>, <code>transfer</code>, <code>transfer_cancel</code>, <code>transfer_failure</code>, or <code>transfer_refund</code>. <a href="https://stripe.com/docs/reports/balance-transaction-types">Learn more</a> about balance transaction types and what they represent. If you are looking to classify transactions for accounting purposes, you might want to consider <code>reporting_category</code> instead.
        'payout' => null, // string|null
    ];

    public static function prefix(): string
    {
        return 'txn';
    }



    protected static array $expandableProps = ['payout'];
}
