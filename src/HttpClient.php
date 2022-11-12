<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Readdle\StripeHttpClientMock\Error\Unauthorized;
use Stripe\ApiResource;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;

final class HttpClient implements ClientInterface
{
    private static bool $debug = false;
    private static bool $mock = true;

    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public static function mock(): void
    {
        self::$mock = true;
    }

    /** @noinspection SpellCheckingInspection */
    public static function unmock(): void
    {
        self::$mock = false;
    }

    public static function debug(bool $mode = true): void
    {
        self::$debug = $mode;
    }

    private static function resolveAction(string $method, bool $idPresent): string
    {
        return match ($method) {
            'post' => $idPresent ? 'update' : 'create',
            'get' => $idPresent ? 'retrieve' : 'list',
            'delete' => 'delete',
            default => '',
        };
    }

    private static function resolveEntityName(string $entityName): string
    {
        if ($entityName[strlen($entityName) - 1] !== 's') {
            return $entityName;
        }

        return substr($entityName, 0, -1); // remove trailing "s" to make entity name singular
    }

    /**
     * @throws Exception
     */
    #[ArrayShape(['action' => 'string', 'entity' => 'string', 'entityId' => 'string', 'filter' => '?array'])]
    private function resolveActionAndEntity(string $method, string $absUrl): array
    {
        $result = [];

        $path = ltrim(str_replace(ApiResource::baseUrl(), '', $absUrl), '/');
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

        if (array_key_exists('subEntity', $result) && array_key_exists('subEntityId', $result)) {
            // this means that action is actually performed on the related entity,
            // so we use it as a primary entity to perform operation on it and add filter by main entity's id
            $result['filter'][$result['entity']] = $result['entityId'];
            $result['entity'] = $result['subEntity'];
            $result['entityId'] = $result['subEntityId'];
            unset($result['subEntity'], $result['subEntityId']);
        } elseif (array_key_exists('subAction', $result)) {
            $result['action'] = $result['subAction'];
            unset($result['subAction']);
        }

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
        self::printf('HttpClient: %s %s', strtoupper($method), $absUrl);

        try {
            $actionAndEntity = $this->resolveActionAndEntity($method, $absUrl);
        } catch (Exception) {
            self::printf('HttpClient: could not resolve action/entity');
        }

        if (isset($actionAndEntity)) {
            if (isset($actionAndEntity['filter'])) {
                $params = array_merge($params, $actionAndEntity['filter']);
            }

            self::printf(
                'HttpClient: %s %s id=%s %s',
                $actionAndEntity['action'],
                $actionAndEntity['entity'],
                $actionAndEntity['entityId'],
                $params
            );
        }

        if (self::$mock) {
            if (isset($actionAndEntity)) {
                [$responseText, $httpStatusCode, $responseHeaders] = $this->requestMocked(
                    $actionAndEntity['action'],
                    $actionAndEntity['entity'],
                    $actionAndEntity['entityId'],
                    $headers,
                    $params
                );
            } else {
                $responseText = '{"error":{}}';
                $httpStatusCode = 500;
                $responseHeaders = [];
            }

        } else {
            [$responseText, $httpStatusCode, $responseHeaders] = $this->requestReal(
                $method,
                $absUrl,
                $headers,
                $params,
                $hasFile
            );
        }

        self::printf(
            '%s response (%s): %s',
            self::$mock ? 'HttpClient' : 'Real Stripe',
            $httpStatusCode,
            $responseText
        );

        return [$responseText, $httpStatusCode, $responseHeaders];
    }

    private function requestReal($method, $absUrl, $headers, $params, $hasFile): array
    {
        [$responseText, $httpStatusCode, $headers] = CurlClient::instance()->request(
            $method,
            $absUrl,
            $headers,
            $params,
            $hasFile
        );

        if (self::$debug) {
            // minifying to have shorter log
            $responseText = json_encode(json_decode($responseText, true));
        }

        return [$responseText, $httpStatusCode, $headers];
    }

    private function requestMocked(
        string $action,
        string $entity,
        ?string $entityId,
        array $headers,
        array $params
    ): array {
        $extractedApiKey = self::extractApiKey($headers);

        if ($extractedApiKey !== $this->apiKey) {
            $error = new Unauthorized($extractedApiKey);

            return [
                $error->toString(),
                $error->getHttpStatusCode(),
                []
            ];
        }

        try {
            $response = EntityManager::handleAction($action, $entity, $entityId, $params);
        } catch (Exception $e) {
            self::printf("EntityManager exception: %s\n\n%s", $e->getMessage(), $e->getTraceAsString());
            return ['{"error":{}}', 500, []];
        }

        return [$response->toString(), $response->getHttpStatusCode(), []];
    }

    public static function printf(string $format, ...$args): void
    {
        if (!self::$debug) {
            return;
        }

        foreach ($args as &$arg) {
            if (is_array($arg) || is_object($arg)) {
                $arg = var_export($arg, true);
            }
        }

        fwrite(STDOUT, vsprintf($format, $args) . "\n\n");
    }
}
