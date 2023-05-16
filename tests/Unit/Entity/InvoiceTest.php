<?php

namespace Unit\Entity;


use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Collection;
use Readdle\StripeHttpClientMock\Entity\Customer;
use Readdle\StripeHttpClientMock\Entity\Price;
use Readdle\StripeHttpClientMock\Entity\Subscription;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\HttpClient;
use Stripe\ApiRequestor;
use Stripe\StripeClient;

/**
 * User: tarjei
 * Date: 16.05.2023 / 09:28
 */
class InvoiceTest extends TestCase
{

    public function testPayInvoice()
    {
        $customer = EntityManager::createEntity('customer', []);
        $price = EntityManager::createEntity('price',['unit_amount' => 1000, 'currency' => 'usd']);

        $subscription = EntityManager::createEntity('subscription',[
            'customer' => $customer->id,
            'items' => [
                ['price' => $price->id, 'amount' => 1, 'plan' => 'myplan',],
            ],
            'metadata' => ['courseId' => 1],
        ]);

        EntityManager::createEntity('invoice', [
            'customer' => $customer->id,
            'subscription' => $subscription->id,
            'status' => \Stripe\Invoice::STATUS_OPEN,
        ]);
        /** @var Collection $invoices */
        $invoices = EntityManager::listEntity('invoice', ['customer' => $customer->id]);


        $this->assertCount(2, $invoices->data);

        $invoice = $invoices->data[1];

        ApiRequestor::setHttpClient(new HttpClient('key'));
        $client = new StripeClient('key');

        $res = $client->invoices->pay($invoice->id, ['payment_method' => 'pm_card_visa']);

        $this->assertEquals('paid', $res->status);


    }

}
