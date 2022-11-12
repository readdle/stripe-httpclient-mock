<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\Error\ResourceMissing;
use Readdle\StripeHttpClientMock\ResponseInterface;

class PaymentMethod extends AbstractEntity
{
    protected array $props = [
        'billing_details' => [],
        'card'            => [],
        'created'         => null,
        'customer'        => null,
        'livemode'        => false,
        'metadata'        => [],
        'type'            => null,
    ];

    protected static array $subActions = [
        'attach' => 'attachCustomer',
        'detach' => 'detachCustomer',
    ];

    public static function prefix(): string
    {
        return 'pm';
    }

    /** @noinspection PhpUnused */
    public function attachCustomer(array $params): PaymentMethod
    {
        $this->props['customer'] = $params['customer'];
        return $this;
    }

    /** @noinspection PhpUnused */
    public function detachCustomer(): ResponseInterface
    {
        if (empty($this->props['customer'])) {
            return new ResourceMissing();
        }

        $customer = EntityManager::retrieveEntity('customer', $this->props['customer']);

        if ($customer instanceof AbstractEntity) {
            /** @noinspection PhpUndefinedFieldInspection */
            $invoiceSettings = $customer->invoice_settings;

            if ($invoiceSettings && $invoiceSettings['default_payment_method'] === $this->props['id']) {
                $invoiceSettings['default_payment_method'] = null;
                /** @noinspection PhpUndefinedFieldInspection */
                $customer->invoice_settings = $invoiceSettings;
            }
        }

        $this->props['customer'] = null;
        return $this;
    }
}
