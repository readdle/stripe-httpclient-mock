<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

class Payout extends AbstractEntity
{
    protected array $props = [
        'amount' => 0, //  int Amount (in %s) to be transferred to your bank account or debit card.
        'arrival_date' => 0, //  int Date the payout is expected to arrive in the bank. This factors in delays like weekends or bank holidays.
        'automatic' => true, //  bool Returns <code>true</code> if the payout was created by an <a href="https://stripe.com/docs/payouts#payout-schedule">automated payout schedule</a>, and <code>false</code> if it was <a href="https://stripe.com/docs/payouts#manual-payouts">requested manually</a>.
        'balance_transaction' => 'txn_xx', //  null|string|\Stripe\BalanceTransaction ID of the balance transaction that describes the impact of this payout on your account balance.
        'created' => 0, //  int Time at which the object was created. Measured in seconds since the Unix epoch.
        'currency' => 'usd', //  string Three-letter <a href="https://www.iso.org/iso-4217-currency-codes.html">ISO currency code</a>, in lowercase. Must be a <a href="https://stripe.com/docs/currencies">supported currency</a>.
        'description' => 'STRIPE_PAYOUT', //  null|string An arbitrary string attached to the object. Often useful for displaying to users.
        'destination' => null, //  null|string|\Stripe\BankAccount|\Stripe\Card ID of the bank account or card the payout was sent to.
        'failure_balance_transaction' => null, //  null|string|\Stripe\BalanceTransaction If the payout failed or was canceled, this will be the ID of the balance transaction that reversed the initial balance transaction, and puts the funds from the failed payout back in your balance.
        'failure_code' => null, //  null|string Error code explaining reason for payout failure if available. See <a href="https://stripe.com/docs/api#payout_failures">Types of payout failures</a> for a list of failure codes.
        'failure_message' => 0, //  null|string Message to user further explaining reason for payout failure if available.
        'livemode' => false, //  bool Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
        'metadata' => null, //  null|\Stripe\StripeObject Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
        'method' => 'standard', //  string The method used to send this payout, which can be <code>standard</code> or <code>instant</code>. <code>instant</code> is only supported for payouts to debit cards. (See <a href="https://stripe.com/blog/instant-payouts-for-marketplaces">Instant payouts for marketplaces</a> for more information.)
        'original_payout' => null, //  null|string|\Stripe\Payout If the payout reverses another, this is the ID of the original payout.
        'reconciliation_status' => 'not_applicable', //  string If <code>completed</code>, the <a href="https://stripe.com/docs/api/balance_transactions/list#balance_transaction_list-payout">Balance Transactions API</a> may be used to list all Balance Transactions that were paid out in this payout.
        'reversed_by' => null, //  null|string|\Stripe\Payout If the payout was reversed, this is the ID of the payout that reverses this payout.
        'source_type' => 'card', //  string The source balance this payout came from. One of <code>card</code>, <code>fpx</code>, or <code>bank_account</code>.
        'statement_descriptor' => null, //  null|string Extra information about a payout to be displayed on the user's bank statement.
        'status' => 'paid', //  string Current status of the payout: <code>paid</code>, <code>pending</code>, <code>in_transit</code>, <code>canceled</code> or <code>failed</code>. A payout is <code>pending</code> until it is submitted to the bank, when it becomes <code>in_transit</code>. The status then changes to <code>paid</code> if the transaction goes through, or to <code>failed</code> or <code>canceled</code> (within 5 business days). Some failed payouts may initially show as <code>paid</code> but then change to <code>failed</code>.
        'type' => 'card', //  string Can be <code>bank_account</code> or <code>card</code>.
    ];

    public static function prefix(): string
    {
        return 'po';
    }

    //protected static array $expandableProps = [    ];
}
