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
        'parent'                           => null,
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
        'parent',
    ];

    // Real Stripe rejects `parent.subscription_details.subscription` outright on the invoice *list*
    // endpoint - it's only allowed on a single retrieve/action - so this mirrors that restriction.
    protected static array $listRestrictedExpandableProps = [
        'parent',
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

        /** @var Invoice $entity */
        $entity = parent::create($id, $props);
        $entity->rebuildParent();

        return $entity;
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

        $entity = parent::update($props);
        $this->rebuildParent();

        return $entity;
    }

    /**
     * Stripe API "Basil" (2025-03-31) replaced Invoice's old top level `subscription` field with
     * `parent.subscription_details.subscription`. `subscription` is kept as an internal-only prop
     * above (existing code in this library - Subscription::create(), the `subscription` list filter -
     * still reads/writes it directly), and `parent` is (re)built from it here whenever it changes, as
     * a real InvoiceParent value object rather than a plain array, so EntityManager::expand() can
     * recurse into `parent.subscription_details.subscription` through the normal
     * $expandableProps/howToExpand() mechanism like it does for any other entity - see InvoiceParent.
     */
    private function rebuildParent(): void
    {
        $subscriptionId = $this->props['subscription'] ?? null;

        if (empty($subscriptionId)) {
            $this->props['parent'] = null;

            return;
        }

        /** @var InvoiceSubscriptionDetails $subscriptionDetails */
        $subscriptionDetails = InvoiceSubscriptionDetails::create('', ['subscription' => $subscriptionId]);

        /** @var InvoiceParent $parent */
        $parent = InvoiceParent::create('', [
            'type'                 => 'subscription_details',
            'subscription_details' => $subscriptionDetails,
        ]);

        $this->props['parent'] = $parent;
    }

    /**
     * Stripe API "Basil" also removed the top level `charge`/`payment_intent`/`tax` fields from the
     * Invoice object, in favor of `payments`/`total_taxes`. Unlike `parent` above, nothing needs to
     * recurse through these via expand (this library always embeds them fully - see
     * toInvoicePaymentEntity()), so they're computed here at output time instead of kept in sync as
     * real props; `subscription`/`charge`/`payment_intent`/`tax` themselves stay internal-only.
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        $taxAmount = $array['tax'] ?? null;
        unset($array['subscription'], $array['charge'], $array['payment_intent'], $array['tax']);

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
