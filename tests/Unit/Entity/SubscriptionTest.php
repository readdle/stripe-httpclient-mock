<?php
declare(strict_types=1);

namespace Unit\Entity;

use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Entity\Customer;
use Readdle\StripeHttpClientMock\Entity\Subscription;

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
}
