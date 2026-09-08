<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Exception;
use Readdle\StripeHttpClientMock\Collection;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\Error\ResourceMissing;
use Readdle\StripeHttpClientMock\ResponseInterface;

class Invoice extends AbstractEntity
{
    protected array $props = [
        'account_country'                  => null,
        'account_name'                     => null,
        'account_tax_ids'                  => null,
        'amount_due'                       => null,
        'amount_paid'                      => null,
        'amount_remaining'                 => null,
        'application'                      => null,
        'application_fee_amount'           => null,
        'attempt_count'                    => null,
        'attempted'                        => false,
        'auto_advance'                     => true,
        'automatic_tax'                    => [
            'enabled' => false,
            'status'  => null
        ],
        'billing_reason'                   => null,
        'charge'                           => null,
        'collection_method'                => null,
        'created'                          => null,
        'currency'                         => null,
        'custom_fields'                    => null,
        'customer'                         => null,
        'customer_address'                 => null,
        'customer_email'                   => null,
        'customer_name'                    => null,
        'customer_phone'                   => null,
        'customer_shipping'                => null,
        'customer_tax_exempt'              => 'none',
        'customer_tax_ids'                 => [],
        'default_payment_method'           => null,
        'default_source'                   => null,
        'default_tax_rates'                => [],
        'description'                      => null,
        'discount'                         => null,
        'discounts'                        => [],
        'due_date'                         => null,
        'ending_balance'                   => 0,
        'footer'                           => '',
        'hosted_invoice_url'               => null,
        'invoice_pdf'                      => null,
        'last_finalization_error'          => null,
        'lines'                            => [],
        'livemode'                         => false,
        'metadata'                         => [],
        'next_payment_attempt'             => null,
        'number'                           => null,
        'on_behalf_of'                     => null,
        'paid'                             => false,
        'paid_out_of_band'                 => null,
        'payment_intent'                   => null,
        'payment_settings'                 => [
            'default_mandate'        => null,
            'payment_method_options' => null,
            'payment_method_types'   => null
        ],
        'period_end'                       => null,
        'period_start'                     => null,
        'post_payment_credit_notes_amount' => 0,
        'pre_payment_credit_notes_amount'  => 0,
        'quote'                            => null,
        'receipt_number'                   => null,
        'rendering_options'                => null,
        'starting_balance'                 => 0,
        'statement_descriptor'             => null,
        'status'                           => 'draft',
        'status_transitions'               => [
            'finalized_at'            => null,
            'marked_uncollectible_at' => null,
            'paid_at'                 => null,
            'voided_at'               => null
        ],
        'subscription'                     => null,
        'subtotal'                         => 0,
        'subtotal_excluding_tax'           => 0,
        'tax'                              => null,
        'test_clock'                       => null,
        'total'                            => 0,
        'total_discount_amounts'           => [],
        'total_excluding_tax'              => 0,
        'total_tax_amounts'                => [],
        'transfer_data'                    => null,
        'webhooks_delivered_at'            => null,
    ];

    protected static array $expandableProps = [
        'customer',
        'payment_intent',
        'subscription',
    ];

    protected static array $subActions = [
        'finalize'       => 'finalize',
        'void'           => 'void',
        'create_preview' => 'getUpcomingInvoice',
    ];

    public static function prefix(): string
    {
        return 'in';
    }

    /**
     * @throws Exception
     */
    public static function create(string $id, array $props = []): ResponseInterface
    {
        if (!array_key_exists('payment_intent', $props)) {
            $paymentIntent = EntityManager::createEntity('payment_intent', [
                'amount'   => 1,
                'currency' => 'usd',
            ]);
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $props['payment_intent'] = $paymentIntent->id;
        }

        if (array_key_exists('default_tax_rates', $props)) {
            $props['default_tax_rates'] = array_map(
                fn ($defaultTaxRate) =>
                    is_array($defaultTaxRate)
                        ? $defaultTaxRate
                        : EntityManager::retrieveEntity('tax_rate', $defaultTaxRate)->toArray(),
                $props['default_tax_rates']
            );
        }

        if (!array_key_exists('lines', $props)) {
            $lines = new Collection();
            $pendingInvoiceItems = [];

            /** @noinspection SpellCheckingInspection */
            $invoiceItems = EntityManager::listEntity('invoiceitem', ['customer' => $props['customer']]);

            if (!$invoiceItems instanceof ResourceMissing) {
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $pendingInvoiceItems = $invoiceItems->data;
            }

            if (!empty($pendingInvoiceItems)) {
                foreach ($pendingInvoiceItems as $pendingInvoiceItem) {
                    $lines->add(LineItem::createFromInvoiceItem($pendingInvoiceItem));
                }
            }

            $props['lines'] = $lines->toArray();
        }

        return parent::create($id, $props);
    }

    public function update(array $props): ResponseInterface
    {
        if (array_key_exists('default_tax_rates', $props)) {
            $props['default_tax_rates'] = array_map(
                fn ($defaultTaxRate) =>
                is_array($defaultTaxRate)
                    ? $defaultTaxRate
                    : EntityManager::retrieveEntity('tax_rate', $defaultTaxRate)->toArray(),
                $props['default_tax_rates']
            );
        }

        return parent::update($props);
    }

    /**
     * Real Stripe only turns `parent.subscription_details.subscription` into a full Subscription
     * object when it's explicitly expanded (it's a plain string ID by default, same as the old
     * `subscription` field). EntityManager::expand() special-cases this path (it isn't a plain prop
     * the generic expand walker can follow) and calls this to record that this specific instance was
     * expanded, since $props alone can't tell toArray() whether to embed the string or the object.
     */
    private bool $subscriptionExpanded = false;

    public function markSubscriptionExpanded(): void
    {
        $this->subscriptionExpanded = true;
    }

    /**
     * Stripe API "Basil" (2025-03-31) removed the top level `subscription`, `charge`, `payment_intent`
     * and `tax` fields from the Invoice object. We keep them as internal props above (existing code in
     * this library - e.g. Subscription::create(), the `subscription`/`coupon` list filters - still reads
     * and writes them directly), but the wire response needs to reflect the new shape, so it's computed
     * here instead of being part of $props.
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $subscriptionId = $array['subscription'] ?? null;
        $taxAmount = $array['tax'] ?? null;
        unset($array['subscription'], $array['charge'], $array['payment_intent'], $array['tax']);

        $subscriptionValue = $subscriptionId;
        if (!empty($subscriptionId) && $this->subscriptionExpanded) {
            $subscriptionEntity = EntityManager::retrieveEntity('subscription', $subscriptionId);
            $subscriptionValue = $subscriptionEntity instanceof AbstractEntity
                ? $subscriptionEntity->toArray()
                : $subscriptionId;
        }

        $array['parent'] = empty($subscriptionId) ? null : [
            'type'                 => 'subscription_details',
            'subscription_details' => [
                'metadata'                    => null,
                'subscription'                => $subscriptionValue,
                'subscription_proration_date' => null,
            ],
        ];

        $invoicePayment = $this->toInvoicePaymentEntity();
        $array['payments'] = [
            'object'   => 'list',
            'data'     => $invoicePayment ? [$invoicePayment->toArray()] : [],
            'has_more' => false,
            'url'      => '/v1/invoice_payments',
        ];

        $array['total_taxes'] = empty($taxAmount) ? [] : [[
            'amount'             => $taxAmount,
            'tax_behavior'       => 'exclusive',
            'tax_rate_details'   => null,
            'taxability_reason'  => null,
            'taxable_amount'     => null,
            'type'               => 'tax_rate',
        ]];

        return $array;
    }

    /**
     * Builds the InvoicePayment representing this invoice's (single, default) payment, used both to
     * embed `payments.data[0]` above and by EntityManager's `/v1/invoice_payments` list (which is how
     * Charge/PaymentIntent - no longer carrying a direct `invoice` back-reference - find their invoice).
     *
     * @param bool $expandInvoice When true, embeds this invoice fully (for the `/v1/invoice_payments`
     *                            list); when false (embedding into this same invoice's own `payments`
     *                            field) only the id is used, to avoid infinite recursion.
     */
    public function toInvoicePaymentEntity(bool $expandInvoice = false): ?InvoicePayment
    {
        if (empty($this->props['charge']) && empty($this->props['payment_intent'])) {
            return null;
        }

        $payment = ['type' => !empty($this->props['payment_intent']) ? 'payment_intent' : 'charge'];

        if (!empty($this->props['charge'])) {
            $chargeEntity = EntityManager::retrieveEntity('charge', $this->props['charge']);
            $payment['charge'] = $chargeEntity instanceof AbstractEntity ? $chargeEntity->toArray() : $this->props['charge'];
        }

        if (!empty($this->props['payment_intent'])) {
            $paymentIntentEntity = EntityManager::retrieveEntity('payment_intent', $this->props['payment_intent']);
            $payment['payment_intent'] = $paymentIntentEntity instanceof AbstractEntity
                ? $paymentIntentEntity->toArray()
                : $this->props['payment_intent'];
        }

        /** @var InvoicePayment */
        return InvoicePayment::create('ip_' . $this->props['id'], [
            'amount_paid'      => $this->props['amount_paid'] ?? null,
            'amount_requested' => $this->props['amount_due'] ?? null,
            'created'          => $this->props['created'] ?? null,
            'currency'         => $this->props['currency'] ?? null,
            'invoice'          => $expandInvoice ? $this->toArray() : $this->props['id'],
            'is_default'       => true,
            'payment'          => $payment,
            'status'           => 'paid',
        ]);
    }

    public static function parseUrlTail(string $tail): array
    {
        $parsedTail = parent::parseUrlTail($tail);

        if (array_key_exists('entityId', $parsedTail) && $parsedTail['entityId'] === 'create_preview') {
            $parsedTail['subAction'] = $parsedTail['entityId'];
            $parsedTail['entityId'] = null;
        }

        return $parsedTail;
    }

    /** @noinspection PhpUnused */
    public function finalize(): Invoice
    {
        $this->props['status'] = 'open';
        return $this;
    }

    public function void(): Invoice
    {
        $this->props['status'] = 'void';
        return $this;
    }

    /**
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function getUpcomingInvoice(array $params): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = self::create('', ['customer' => $params['customer']]);
        $lines = new Collection();

        $items = $params['invoice_items'] ?? $params['subscription_details']['items'] ?? [];

        foreach ($items as $invoiceItemData) {
            if (array_key_exists('price', $invoiceItemData)) {
                $invoiceItemData['price'] = EntityManager::retrieveEntity('price', $invoiceItemData['price']);
            }

            if (array_key_exists('tax_rates', $invoiceItemData)) {
                $invoiceItemData['tax_rates'] = array_map(
                    fn ($taxRateId) => EntityManager::retrieveEntity('tax_rate', $taxRateId),
                    $invoiceItemData['tax_rates']
                );
            }

            /** @noinspection SpellCheckingInspection */
            /** @var InvoiceItem $invoiceItem */
            $invoiceItem = EntityManager::createEntity('invoiceitem', array_merge(
                ['customer' => $params['customer']],
                $invoiceItemData,
            ));

            $lines->add($invoiceItem);
        }

        $invoice->props['lines'] = $lines->toArray();

        return $invoice;
    }
}
