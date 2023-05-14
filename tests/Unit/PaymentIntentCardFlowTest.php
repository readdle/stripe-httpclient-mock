<?php

namespace Unit;


use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\HttpClient;
use Stripe\ApiRequestor;
use Stripe\Exception\CardException;
use Stripe\StripeClient;

/**
 * User: tarjei
 * Date: 14.05.2023 / 13:31
 */
class PaymentIntentCardFlowTest extends TestCase
{

    private StripeClient $client;

    public function setUp(): void
    {
        parent::setUp();

        ApiRequestor::setHttpClient(new HttpClient('key'));
        $this->client = new StripeClient('key');
    }

    public function testSimpleConfirmSuccess()
    {
        $paymentIntent = $this->client->paymentIntents->create([
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        $this->assertEquals('requires_confirmation', $paymentIntent->status);

        $paymentIntent = $this->client->paymentIntents->confirm($paymentIntent->id, [
            'payment_method' => 'pm_card_visa',
        ]);

        $this->assertEquals('succeeded', $paymentIntent->status);

    }

    public function testSimpleConfirmDecline()
    {
        $paymentIntent = $this->client->paymentIntents->create([
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        $this->assertEquals('requires_confirmation', $paymentIntent->status);

        try {
        $this->client->paymentIntents->confirm($paymentIntent->id, [
            'payment_method' => 'pm_card_visa_chargeDeclined',
        ]);
        } catch (CardException $e ) {
            $this->assertNotNull($e->getStripeCode());
            $this->assertNotNull($e->getDeclineCode());

            $paymentIntent = $this->client->paymentIntents->retrieve($paymentIntent->id);
            $this->assertEquals("requires_payment_method", $paymentIntent->status);
            $this->assertEquals("generic_decline", $paymentIntent->last_payment_error->decline_code);

        }
    }

}