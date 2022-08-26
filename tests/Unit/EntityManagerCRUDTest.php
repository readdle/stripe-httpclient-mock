<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Readdle\StripeHttpClientMock\Entity\PromotionCode;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\Error\ResourceMissing;
use Readdle\StripeHttpClientMock\Success\Deleted;

class EntityManagerCRUDTest extends TestCase
{
    private static string $promoCodeId = '';

    /**
     * @throws Exception
     */
    public function testCreateEntity(): void
    {
        $promoCode = EntityManager::createEntity('promotion_code', []);
        $this->assertNotEmpty($promoCode);
        $this->assertInstanceOf(PromotionCode::class, $promoCode);
        $this->assertIsString($promoCode->code);
        $this->assertEquals('promotion_code', $promoCode->object);
        $this->assertTrue(str_starts_with($promoCode->id, PromotionCode::prefix()));
        self::$promoCodeId = $promoCode->id;
    }

    public function testRetrieveEntity(): void
    {
        $promoCode = EntityManager::retrieveEntity('promotion_code', self::$promoCodeId);
        $this->assertNotEmpty($promoCode);
        $this->assertInstanceOf(PromotionCode::class, $promoCode);
    }

    public function testUpdateEntity(): void
    {
        EntityManager::updateEntity('promotion_code', self::$promoCodeId, [
            'active'   => false,
            'code'     => 'promo',
            'metadata' => [
                'test' => 'value',
            ],
        ]);

        $promoCode = EntityManager::retrieveEntity('promotion_code', self::$promoCodeId);

        $this->assertNotEmpty($promoCode);
        $this->assertInstanceOf(PromotionCode::class, $promoCode);
        $this->assertEquals(false, $promoCode->active);
        $this->assertEquals('promo', $promoCode->code);
        $this->assertIsArray($promoCode->metadata);
        $this->assertArrayHasKey('test', $promoCode->metadata);
        $this->assertEquals('value', $promoCode->metadata['test']);
    }

    public function testDeleteEntity(): void
    {
        $deleted = EntityManager::deleteEntity('promotion_code', self::$promoCodeId);
        $this->assertNotEmpty($deleted);
        $this->assertInstanceOf(Deleted::class, $deleted);
        $this->assertEquals(self::$promoCodeId, $deleted->id);
        $this->assertEquals('promotion_code', $deleted->object);
        $this->assertTrue($deleted->deleted);
    }

    public function testErrorResponses(): void
    {
        $assertError = function ($result) {
            /** @var ResourceMissing $result */
            $this->assertNotEmpty($result);
            $this->assertInstanceOf(ResourceMissing::class, $result);
            $this->assertIsArray($result->error);
            $this->assertArrayHasKey('code', $result->error);
            $this->assertEquals('resource_missing', $result->error['code']);
            $this->assertArrayHasKey('type', $result->error);
            $this->assertEquals('invalid_request_error', $result->error['type']);
        };

        $assertError(EntityManager::retrieveEntity('promotion_code', 'id'));
        $assertError(EntityManager::updateEntity('promotion_code', 'id', []));
        $assertError(EntityManager::deleteEntity('promotion_code', 'id'));
    }
}
