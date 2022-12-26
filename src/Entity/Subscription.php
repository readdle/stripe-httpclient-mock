<?php
/** @noinspection PhpUndefinedFieldInspection */
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use DateTime;
use Exception;
use Readdle\StripeHttpClientMock\Collection;
use Readdle\StripeHttpClientMock\EntityManager;

class Subscription extends AbstractEntity
{
    protected array $props = [
        'application'                       => null,
        'application_fee_percent'           => null,
        'automatic_tax'                     => [
            'enabled' => false
        ],
        'billing_cycle_anchor'              => null,
        'billing_thresholds'                => null,
        'cancel_at'                         => null,
        'cancel_at_period_end'              => null,
        'canceled_at'                       => null,
        'collection_method'                 => null,
        'created'                           => null,
        'currency'                          => null,
        'current_period_end'                => null,
        'current_period_start'              => null,
        'customer'                          => null,
        'days_until_due'                    => null,
        'default_payment_method'            => null,
        'default_source'                    => null,
        'default_tax_rates'                 => [],
        'description'                       => null,
        'discount'                          => null,
        'ended_at'                          => null,
        'items'                             => [],
        'latest_invoice'                    => null,
        'livemode'                          => false,
        'metadata'                          => [],
        'next_pending_invoice_item_invoice' => null,
        'pause_collection'                  => null,
        'payment_settings'                  => [
            'payment_method_options'      => null,
            'payment_method_types'        => null,
            'save_default_payment_method' => null,
        ],
        'pending_invoice_item_interval'     => null,
        'pending_setup_intent'              => null,
        'pending_update'                    => null,
        'schedule'                          => null,
        'start_date'                        => null,
        'status'                            => 'active',
        'test_clock'                        => null,
        'transfer_data'                     => null,
        'trial_end'                         => null,
        'trial_start'                       => null,
    ];

    /**
     * @throws Exception
     */
    public static function create(string $id, array $props = []): AbstractEntity
    {
        /** @var Subscription $entity */
        $entity = parent::create($id, $props);

        $invoiceProps = [
            'subscription' => $id,
        ];

        if (!empty($entity->props['default_tax_rates'])) {
            $invoiceProps['default_tax_rates'] = $entity->props['default_tax_rates'];
            $entity->props['default_tax_rates'] = array_map(
                fn ($taxRateId) => EntityManager::retrieveEntity('tax_rate', $taxRateId)->toArray(),
                $entity->props['default_tax_rates']
            );
        }

        $currency = null;
        $amount = 0;

        if (array_key_exists('items', $props)) {
            $items = new Collection();

            foreach ($props['items'] as $item) {
                $subscriptionItem = EntityManager::createEntity('subscription_item', $item);

                if (!$subscriptionItem instanceof AbstractEntity) {
                    continue;
                }

                $currency = $subscriptionItem->price->currency;
                $amount += (float) $subscriptionItem->price->unit_amount;
                $items->add($subscriptionItem);
            }

            $entity->props['items'] = $items->toArray();
        } else {
            $entity->props['items'] = (new Collection())->toArray();
        }

        if (array_key_exists('promotion_code', $props)) {
            /** @var PromotionCode $promoCode */
            $promoCode = EntityManager::retrieveEntity('promotion_code', $props['promotion_code']);
            $discount = EntityManager::createEntity('discount', [
                'coupon'         => $promoCode->coupon,
                'promotion_code' => $props['promotion_code'],
            ]);
            $entity->props['discount'] = $discount->toArray();
        }


        if (array_key_exists('customer', $props)) {
            $invoiceProps['customer'] = $props['customer'];
        }

        if (array_key_exists('metadata', $props)) {
            $invoiceProps['metadata'] = $props['metadata'];
        }

        $invoiceProps['paid'] = true;
        $invoiceProps['status'] = 'paid';
        $invoiceProps['currency'] = $currency;
        $invoiceProps['total'] = $amount;
        /** @var Invoice $invoice */
        $invoice = EntityManager::createEntity('invoice', $invoiceProps);
        $entity->props['latest_invoice'] = $invoice->id;

        $paymentIntent = EntityManager::retrieveEntity('payment_intent', $invoice->payment_intent);

        if ($paymentIntent instanceof AbstractEntity) {
            $paymentIntent->update([
                'amount' => array_reduce(
                    $entity->props['items']['data'],
                    fn ($sum, $item) => $sum + $item['price']['unit_amount'],
                    0
                ),
                'currency' => $entity->props['currency'],
            ]);
        }

        if (
            empty($entity->props['current_period_start']) &&
            empty($entity->props['current_period_end'])
        ) {
            $dt = new DateTime('now');
            $entity->props['current_period_start'] = $dt->getTimestamp();
            $entity->props['current_period_end'] = $dt->modify('+1 year')->getTimestamp();
        }

        return $entity;
    }

    public static function prefix(): string
    {
        return 'sub';
    }

    public static function howToExpand(string $propertyName): ?array
    {
        $expandMap = [
            'latest_invoice' => [
                'target'   => 'objectSearcher',
                'object'   => 'invoice',
                'searcher' => function ($subscription, $invoices) {
                    $matchingInvoices = array_filter(
                        $invoices,
                        fn ($invoice) => $invoice->subscription === $subscription->id
                    );

                    if (empty($matchingInvoices)) {
                        return null;
                    }

                    return $matchingInvoices[array_key_last($matchingInvoices)];
                }
            ],
        ];

        return array_key_exists($propertyName, $expandMap) ? $expandMap[$propertyName] : null;
    }
}
