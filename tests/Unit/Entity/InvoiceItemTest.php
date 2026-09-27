<?php
declare(strict_types=1);

namespace Unit\Entity;

use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Entity\Invoice;
use Readdle\StripeHttpClientMock\Entity\InvoiceItem;
use Readdle\StripeHttpClientMock\EntityManager;

class InvoiceItemTest extends TestCase
{
    /**
     * @throws Exception
     */
    private function createCustomerWithInvoice(string $suffix): array
    {
        $customer = EntityManager::createEntity('customer', ['id' => "cus_ii_$suffix"]);
        $price = EntityManager::createEntity('price', [
            'id'          => "price_ii_$suffix",
            'currency'    => 'usd',
            'unit_amount' => 9999,
        ]);
        /** @var Invoice $invoice */
        $invoice = EntityManager::createEntity('invoice', [
            'id'       => "in_ii_$suffix",
            'customer' => $customer->id,
        ]);

        return [$customer, $price, $invoice];
    }

    /**
     * Stripe API "Basil" replaced the flat `price` param with a nested `pricing` object.
     *
     * @throws Exception
     */
    public function testPriceIsTakenFromPricing(): void
    {
        [$customer, $price, $invoice] = $this->createCustomerWithInvoice('1');

        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = EntityManager::createEntity('invoiceitem', [
            'customer' => $customer->id,
            'invoice'  => $invoice->id,
            'pricing'  => ['price' => $price->id],
        ]);

        $this->assertEquals($price->id, $invoiceItem->price);
        $this->assertEquals($price->id, $invoice->lines['data'][0]->price->id);
    }

    /**
     * @throws Exception
     */
    public function testFlatPriceParamIsStillAccepted(): void
    {
        [$customer, $price, $invoice] = $this->createCustomerWithInvoice('2');

        /** @var InvoiceItem $invoiceItem */
        $invoiceItem = EntityManager::createEntity('invoiceitem', [
            'customer' => $customer->id,
            'invoice'  => $invoice->id,
            'price'    => $price->id,
        ]);

        $this->assertEquals($price->id, $invoiceItem->price);
    }
}
