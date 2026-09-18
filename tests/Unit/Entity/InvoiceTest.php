<?php
declare(strict_types=1);

namespace Unit\Entity;

use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Entity\Invoice;
use Readdle\StripeHttpClientMock\Entity\Subscription;
use Readdle\StripeHttpClientMock\EntityManager;

class InvoiceTest extends TestCase
{
    /**
     * @throws Exception
     */
    private function createCustomerWithSubscription(string $suffix): array
    {
        $customer = EntityManager::createEntity('customer', ['id' => "cus_parent_$suffix"]);
        /** @var Subscription $subscription */
        $subscription = EntityManager::createEntity('subscription', [
            'id'       => "sub_parent_$suffix",
            'customer' => $customer->id,
        ]);

        return [$customer, $subscription];
    }

    /**
     * @throws Exception
     */
    public function testParentSubscriptionDefaultsToAnId(): void
    {
        [, $subscription] = $this->createCustomerWithSubscription('1');

        /** @var Invoice $invoice */
        $invoice = EntityManager::retrieveEntity('invoice', $subscription->latest_invoice);

        $this->assertIsString($invoice->parent->subscription_details->subscription);
        $this->assertEquals($subscription->id, $invoice->parent->subscription_details->subscription);
    }

    /**
     * @throws Exception
     */
    public function testParentSubscriptionExpandsToAFullObject(): void
    {
        [, $subscription] = $this->createCustomerWithSubscription('2');

        /** @var Invoice $invoice */
        $invoice = EntityManager::retrieveEntity('invoice', $subscription->latest_invoice, [
            'expand' => ['parent.subscription_details.subscription'],
        ]);

        $this->assertInstanceOf(Subscription::class, $invoice->parent->subscription_details->subscription);
        $this->assertEquals($subscription->id, $invoice->parent->subscription_details->subscription->id);
    }

    /**
     * @throws Exception
     */
    public function testParentSubscriptionDoesNotExpandOnList(): void
    {
        [$customer, $subscription] = $this->createCustomerWithSubscription('3');

        $invoices = EntityManager::listEntity('invoice', [
            'customer' => $customer->id,
            'expand'   => ['data.parent.subscription_details.subscription'],
        ]);

        /** @var Invoice $invoice */
        $invoice = $invoices->data[0];

        $this->assertIsString($invoice->parent->subscription_details->subscription);
        $this->assertEquals($subscription->id, $invoice->parent->subscription_details->subscription);
    }

    /**
     * Expanding one retrieved copy of an invoice must not leak into another (e.g. the copy still
     * sitting in the entity store, or an earlier/later unexpanded retrieve of the same invoice).
     *
     * @throws Exception
     */
    public function testExpandingOneRetrieveDoesNotMutateOthers(): void
    {
        [, $subscription] = $this->createCustomerWithSubscription('4');

        EntityManager::retrieveEntity('invoice', $subscription->latest_invoice, [
            'expand' => ['parent.subscription_details.subscription'],
        ]);

        /** @var Invoice $invoiceAfter */
        $invoiceAfter = EntityManager::retrieveEntity('invoice', $subscription->latest_invoice);

        $this->assertIsString($invoiceAfter->parent->subscription_details->subscription);
    }
}
