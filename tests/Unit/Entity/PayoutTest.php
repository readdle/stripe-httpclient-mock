<?php

namespace Unit\Entity;


use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\HttpClient;
use Stripe\ApiRequestor;
use Stripe\StripeClient;

/**
 * User: tarjei
 * Date: 17.05.2023 / 12:32
 */
class PayoutTest extends TestCase
{
    private StripeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        ApiRequestor::setHttpClient(new HttpClient('key'));
        $this->client = new StripeClient('key');
    }


    public function testFetchPayoutWithTransactions()
    {

        $customer = EntityManager::createEntity('customer', [
            'balance' => 100,
            'currency' => 'usd',
        ]);

        $this->assertNotNull($customer);

        $payout = EntityManager::createEntity('payout', [
            'amount' => 100,
            'currency' => 'usd',
            'arrival_date' => time(),
            'status' => 'paid',
        ]);

        $payout2 = EntityManager::createEntity('payout', [
            'amount' => 100,
            'currency' => 'usd',
            'arrival_date' => time(),
            'status' => 'paid',

        ]);


        $this->assertNotNull($payout->id);

        $tx = EntityManager::createEntity('balance_transaction', [
            'amount' => 100,
            'currency' => 'usd',
            'fee' => 10,
            'net' => 90,
            'source' => 'ch_123',
            'type' => 'charge',
            'payout' => $payout->id,
        ]);

        $this->assertEquals($payout->id, $tx->payout);

        $this->assertEquals(
            1,
            $this->client->balanceTransactions->all(['payout' => $payout->id])->count()
        );

        $this->assertEquals(
            0,
            $this->client->balanceTransactions->all(['payout' => $payout2->id])->count()
        );
    }


}
