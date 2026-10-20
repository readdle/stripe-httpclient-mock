<?php
declare(strict_types=1);

namespace Unit\Entity;

use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\EntityManager;

class PromotionCodeTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testPromotionCodeCreatedWithNestedPromotionCoupon()
    {
        $coupon = EntityManager::createEntity('coupon', ['percent_off' => 100, 'duration' => 'forever']);
        $promoCode = EntityManager::createEntity('promotion_code', [
            'promotion' => ['type' => 'coupon', 'coupon' => $coupon->id],
        ]);

        $this->assertEquals($coupon->id, $promoCode->coupon);
        $this->assertEquals($coupon->id, $promoCode->toArray()['promotion']['coupon']['id']);
    }

    /**
     * @throws Exception
     */
    public function testPromotionCodeFoundByCouponFilter()
    {
        $coupon = EntityManager::createEntity('coupon', ['percent_off' => 100, 'duration' => 'forever']);
        $promoCode = EntityManager::createEntity('promotion_code', [
            'code'      => 'NESTED_PROMO_' . uniqid(),
            'promotion' => ['type' => 'coupon', 'coupon' => $coupon->id],
        ]);

        $list = EntityManager::listEntity('promotion_code', ['coupon' => $coupon->id, 'code' => $promoCode->code]);

        $this->assertCount(1, $list->toArray()['data']);
        $this->assertEquals($promoCode->id, $list->toArray()['data'][0]['id']);
    }
}
