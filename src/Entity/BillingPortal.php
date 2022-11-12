<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Exception;
use Readdle\StripeHttpClientMock\EntityManager;
use Readdle\StripeHttpClientMock\ResponseInterface;

class BillingPortal extends AbstractEntity
{
    protected static array $subActions = [
        'sessions' => 'createSession',
    ];

    /**
     * @throws Exception
     */
    public function createSession(array $params): ResponseInterface
    {
        return EntityManager::createEntity('billing_portal.session', $params);
    }

    public static function parseUrlTail(string $tail): array
    {
        $parsedTail = parent::parseUrlTail($tail);

        if (array_key_exists('entityId', $parsedTail) && $parsedTail['entityId'] === 'sessions') {
            $parsedTail['subAction'] = $parsedTail['entityId'];
            $parsedTail['entityId'] = null;
        }

        return $parsedTail;
    }
}
