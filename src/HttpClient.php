<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock;

use Exception;
use Readdle\StripeHttpClientMock\Error\AbstractError;
use Readdle\StripeHttpClientMock\Error\Unauthorized;
use Stripe\HttpClient\ClientInterface;

final class HttpClient implements ClientInterface
{
    public static bool $debug = false;
    public static string $apiKey = '';
    public static string $apiBase = '';

    private static function resolveAction(string $method, bool $idPresent): string
    {
        switch ($method) {
            case 'post':
                return $idPresent ? 'update' : 'create';

            case 'get':
                return $idPresent ? 'retrieve' : 'list';

            case 'delete':
                return 'delete';

            default:
                return '';
        }
    }

    private static function resolveEntityName(string $entityName): string
    {
        return substr($entityName, 0, -1); // remove trailing "s" to make it singular
    }

    /**
     * @throws Exception
     */
    private function resolveActionAndEntity(string $method, string $absUrl): array
    {
        $result = [];

        $path = ltrim(str_replace(self::$apiBase, '', $absUrl), '/');
        [, $result['entity'], $tail] = explode('/', $path, 3) + [null, null, null];
        $result['entity'] = self::resolveEntityName($result['entity']);

        if (!empty($tail)) {
            $entityClass = EntityManager::resolveEntityClass($result['entity']);
            $tailParts = call_user_func([$entityClass, 'parseUrlTail'], $tail);
            $result['entityId'] = $tailParts['entityId'];

            if (array_key_exists('subAction', $tailParts)) {
                $result['subAction'] = $tailParts['subAction'];
            } elseif (array_key_exists('subEntity', $tailParts)) {
                $result['subEntity'] = self::resolveEntityName($tailParts['subEntity']);
                $result['subEntityId'] = $tailParts['subEntityId'];
            }
        } else {
            $result['entityId'] = null;
        }

        if (array_key_exists('subEntity', $result)) {
            $idPresent = (bool) $result['subEntityId'];
        } else {
            $idPresent = (bool) $result['entityId'];
        }

        $result['action'] = self::resolveAction($method, $idPresent);
        return $result;
    }

    private static function extractApiKey(array $headers): string
    {
        foreach ($headers as $header) {
            if (preg_match('/^Authorization: Bearer (?P<key>\w+)$/', $header, $matches)) {
                return $matches['key'];
            }
        }

        return '';
    }

    final public function request($method, $absUrl, $headers, $params, $hasFile): array
    {
        if (self::$debug) {
            fwrite(STDOUT, "HttpClient: $method -> $absUrl\n");
        }

        $extractedApiKey = self::extractApiKey($headers);

        if ($extractedApiKey !== self::$apiKey) {
            $error = new Unauthorized($extractedApiKey);

            return [
                $error->toString(),
                $error->getHttpStatusCode(),
                []
            ];
        }

        try {
            $actionAndEntity = $this->resolveActionAndEntity($method, $absUrl);
        } catch (Exception $e) {
            return ['{"error":{}}', 500, []];
        }

        $action = $actionAndEntity['action'];

        if (
            array_key_exists('subEntity', $actionAndEntity)
            && array_key_exists('subEntityId', $actionAndEntity)
        ) {
            // it means that action is actually performed on related entity,
            // so we use it as primary entity to perform operation and add filter by main entity's id
            $entity = $actionAndEntity['subEntity'];
            $entityId = $actionAndEntity['subEntityId'];
            $params[$actionAndEntity['entity']] = $actionAndEntity['entityId'];
        } else {
            $entity = $actionAndEntity['entity'];
            $entityId = $actionAndEntity['entityId'];

            if (array_key_exists('subAction', $actionAndEntity)) {
                $action = $actionAndEntity['subAction'];
            }
        }

        if (self::$debug) {
            fwrite(
                STDOUT,
                "HttpClient: $action $entity id=" . var_export($entityId, true)
                . ' ' . var_export($params, true) . "\n\n"
            );
        }

        try {
            $response = EntityManager::handleAction($action, $entity, $entityId, $params);
        } catch (Exception $e) {
            if (self::$debug) {
                fwrite(STDOUT, 'EntityManager exception: ' . $e->getMessage() . "\n");
            }

            return ['{"error":{}}', 500, []];
        }

        if ($response instanceof AbstractError) {
            if (self::$debug) {
                fwrite(STDOUT, 'HttpClient error response: ' . $response->toString() . "\n\n");
            }

            return [$response->toString(), $response->getHttpStatusCode(), []];
        }

        $response = $response->toString();
        $httpStatusCode = 200;

        // [$response, $httpStatusCode] = \Stripe\HttpClient\CurlClient::instance()->request($method, $absUrl, $headers, $params, $hasFile);

        if (self::$debug) {
            fwrite(STDOUT, 'HttpClient response (' . $httpStatusCode . '): ' . $response . "\n\n");
        }

        return [$response, $httpStatusCode, []];
    }
}
