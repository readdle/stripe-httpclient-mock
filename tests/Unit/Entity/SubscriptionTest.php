<?php
declare(strict_types=1);

namespace Unit\Entity;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Entity\Customer;
use Readdle\StripeHttpClientMock\Entity\Subscription;
use Readdle\StripeHttpClientMock\EntityManager;

class SubscriptionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSubscriptionHasDefaultPeriod()
    {
        $customer = Customer::create('cus_123');
        $subscription = Subscription::create('sub_123', [
            'customer' => $customer->id
        ]);

        $this->assertNotNull($subscription->current_period_start);
        $this->assertNotNull($subscription->current_period_end);
    }

    /**
     * @throws Exception
     */
    public function testSubscriptionSupportsCustomPeriod()
    {
        $dt = new DateTime('now');
        $periodStart = $dt->getTimestamp();
        $periodEnd = $dt->modify('+30 days')->getTimestamp();

        $customer = Customer::create('cus_123');
        $subscription = Subscription::create('sub_123', [
            'customer' => $customer->id,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd
        ]);

        $this->assertEquals($periodStart, $subscription->current_period_start);
        $this->assertEquals($periodEnd, $subscription->current_period_end);
    }

    /**
     * @throws Exception
     */
    public function testSubscriptionAppliesPromotionCodeFromDiscounts()
    {
        list($couponId, $promoCodeId) = $this->createPromotionCode();

        $customer = Customer::create('cus_123');
        $subscription = Subscription::create('sub_123', [
            'customer'  => $customer->id,
            'discounts' => [['promotion_code' => $promoCodeId]],
        ]);

        $this->assertNotEmpty($subscription->discount);
        $this->assertEquals($promoCodeId, $subscription->discount['promotion_code']);
        $this->assertEquals($couponId, $subscription->discount['coupon']['id']);
    }

    /**
     * @throws Exception
     */
    public function testSubscriptionAppliesCouponFromDiscounts()
    {
        list($couponId) = $this->createPromotionCode();

        $customer = Customer::create('cus_123');
        $subscription = Subscription::create('sub_123', [
            'customer'  => $customer->id,
            'discounts' => [['coupon' => $couponId]],
        ]);

        $this->assertNotEmpty($subscription->discount);
        $this->assertEquals($couponId, $subscription->discount['coupon']['id']);
    }

    /**
     * The top-level `promotion_code` param was dropped by Stripe API "Basil", but it is still accepted.
     *
     * @throws Exception
     */
    public function testSubscriptionAppliesLegacyPromotionCodeParam()
    {
        list($couponId, $promoCodeId) = $this->createPromotionCode();

        $customer = Customer::create('cus_123');
        $subscription = Subscription::create('sub_123', [
            'customer'       => $customer->id,
            'promotion_code' => $promoCodeId,
        ]);

        $this->assertNotEmpty($subscription->discount);
        $this->assertEquals($promoCodeId, $subscription->discount['promotion_code']);
        $this->assertEquals($couponId, $subscription->discount['coupon']['id']);
    }

    /**
     * @return string[] coupon id and promotion code id
     *
     * @throws Exception
     */
    private function createPromotionCode(): array
    {
        $coupon = EntityManager::createEntity('coupon', ['percent_off' => 100, 'duration' => 'forever']);
        $promoCode = EntityManager::createEntity('promotion_code', ['coupon' => $coupon->id]);

        return [$coupon->id, $promoCode->id];
    }
}
