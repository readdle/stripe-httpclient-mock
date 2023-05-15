<?php

namespace Unit;


use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Entity\Price;
use Readdle\StripeHttpClientMock\HttpClient;
use Readdle\StripeHttpClientMock\TestCards\TestCard\ScaVerificationFlowRequired;
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
        } catch (CardException $e) {
            $this->assertNotNull($e->getStripeCode());
            $this->assertNotNull($e->getDeclineCode());

            $paymentIntent = $this->client->paymentIntents->retrieve($paymentIntent->id);
            $this->assertEquals("requires_payment_method", $paymentIntent->status);
            $this->assertEquals("generic_decline", $paymentIntent->last_payment_error->decline_code);

        }
    }

    public function testSetPaymentMethodOnPiNotInConfirmMethod()
    {
        $paymentIntent = $this->client->paymentIntents->create([
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'payment_method'=> 'pm_card_visa_chargeDeclined',
        ]);

        $this->assertEquals('requires_confirmation', $paymentIntent->status);

        try {
            $this->client->paymentIntents->confirm($paymentIntent->id, []);
        } catch (CardException $e) {
            $this->assertNotNull($e->getStripeCode());
            $this->assertNotNull($e->getDeclineCode());

            $paymentIntent = $this->client->paymentIntents->retrieve($paymentIntent->id);
            $this->assertEquals("requires_payment_method", $paymentIntent->status);
            $this->assertEquals("generic_decline", $paymentIntent->last_payment_error->decline_code);

        }
    }

    public function testCreateSubscription(){
        // Shows what is needed to create a subscription.
        $price = $this->client->prices->create(['unit_amount' => 1000, 'currency' => 'usd']);
        $customer = $this->client->customers->create([]);
        $this->client->subscriptions->create([    'customer' => $customer->id,
                                                  'items' => [
                                                    ['price' => $price->id,'amount' => 1]
                                                  ],
                                                  'metadata' => ['courseId' => 1],
        ]);
    }


    public function testConfirmWithNextAction()
    {
        $paymentIntent = $this->client->paymentIntents->create([
            'amount' => 1000,
            'currency' => 'usd',
            'payment_method_types' => ['card'],
        ]);

        $this->assertEquals('requires_confirmation', $paymentIntent->status);

        $pi = $this->client->paymentIntents->confirm($paymentIntent->id, [
            'payment_method' => ScaVerificationFlowRequired::PAYMENT_METHOD,
        ]);

        $this->assertEquals("requires_action", $pi->status);
        $this->assertEquals("use_stripe_sdk", $pi->next_action->type);
        $this->assertEquals("src_1GxJ5a2eZvKYlo2CJ9jQYQ4X", $pi->next_action->use_stripe_sdk->source);
    }

}
