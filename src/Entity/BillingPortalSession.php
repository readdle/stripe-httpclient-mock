<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Readdle\StripeHttpClientMock\ResponseInterface;

class BillingPortalSession extends AbstractEntity
{
    protected array $props = [
        'configuration' => null,
        'created'       => null,
        'customer'      => null,
        'livemode'      => false,
        'locale'        => null,
        'on_behalf_of'  => null,
        'return_url'    => 'https://example.com',
        'url'           => null,
    ];

    public static function create(string $id, array $props = []): ResponseInterface
    {
        $props['url'] = 'https://billing.stripe.fake/p/session/test_' . uniqid();
        return parent::create($id, $props);
    }

    public static function prefix(): string
    {
        return 'bps';
    }

    public static function objectName(): string
    {
        return 'billing_portal.session';
    }
}
