<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock\Entity;

use Exception;
use Readdle\StripeHttpClientMock\Collection;
use Readdle\StripeHttpClientMock\ResponseInterface;

class AbstractEntity implements ResponseInterface
{
    protected array $props = [];
    protected static array $expandableProps = [];
    /**
     * Names (from $expandableProps) that Stripe only allows expanding via a single retrieve/action,
     * never via a list endpoint - e.g. Invoice::$parent. EntityManager::expand() consults this so a
     * class declares its own real-API quirks rather than the generic walker special-casing them.
     */
    protected static array $listRestrictedExpandableProps = [];
    protected static array $subActions = [];
    protected static array $subEntities = [];

    public static function listRestrictedExpandableProps(): array
    {
        return static::$listRestrictedExpandableProps;
    }

    public function __get(string $key)
    {
        return array_key_exists($key, $this->props) ? $this->props[$key] : null;
    }

    public function __set(string $key, $value): void
    {
        if (!array_key_exists($key, $this->props)) {
            return;
        }

        $this->fillProps([$key => $value]);
    }

    /**
     * clone is normally shallow: it copies $props as an array, but any AbstractEntity *values* held
     * within it (e.g. an embedded value object like InvoiceParent) would still be the same shared
     * instance on both the original and the clone. EntityManager::expand() relies on cloning an
     * entity before mutating it to embed expanded data, so without this, expanding one retrieved
     * copy of an entity could leak that mutation back into every other reference to it (including the
     * one still sitting in the entity store). Cloning recurses (each nested AbstractEntity's own
     * __clone() runs too), so this is safe at any nesting depth.
     */
    public function __clone(): void
    {
        foreach ($this->props as $key => $value) {
            if ($value instanceof AbstractEntity) {
                $this->props[$key] = clone $value;
            }
        }
    }

    public static function create(string $id, array $props = []): ResponseInterface
    {
        $entity = new static();

        $mixedProps = array_merge(
            [
                'id' => $id,
                'object' => static::objectName(),
            ],
            $entity->props,
            $props
        );

        if (array_key_exists('created', $mixedProps) && empty($mixedProps['created'])) {
            $mixedProps['created'] = time();
        }

        $entity->props = [];
        $entity->fillProps($mixedProps);
        return $entity;
    }

    protected function fillProps(array $props): void
    {
        $sampleProps = (new static())->props;
        $sampleProps['id'] = '';
        $sampleProps['object'] = '';

        foreach ($props as $key => $value) {
            if (!array_key_exists($key, $sampleProps)) {
                continue;
            }

            if (is_bool($sampleProps[$key]) && is_string($value)) {
                $value = $value !== 'false';
            }

            if ($value === '') {
                $value = null;
            }

            $this->props[$key] = $value;
        }
    }

    public function update(array $props): ResponseInterface
    {
        $this->fillProps($props);
        return $this;
    }

    /**
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function subAction(string $action, array $params): ResponseInterface
    {
        if (!array_key_exists($action, static::$subActions)) {
            throw new Exception();
        }

        return call_user_func([$this, static::$subActions[$action]], $params);
    }

    public static function parseUrlTail(string $tail): array
    {
        if (!preg_match('/^(?P<entityId>[\w-]+)(?P<subAction>\/[a-z_]+)?(?P<subEntityId>\/\w+)?$/', $tail, $matches)) {
            return [];
        }

        $result = [
            'entityId' => $matches['entityId'],
        ];

        if (!array_key_exists('subAction', $matches)) {
            return $result;
        }

        $result['subAction'] = ltrim($matches['subAction'], '/');

        if (!in_array($result['subAction'], static::$subEntities)) {
            return $result;
        }

        $result['subEntity'] = $result['subAction'];
        unset($result['subAction']);

        $result['subEntityId'] = array_key_exists('subEntityId', $matches)
            ? ltrim($matches['subEntityId'], '/')
            : null;

        return $result;
    }

    public static function prefix(): string
    {
        return strtolower(substr(static::class, strrpos(static::class, '\\') + 1));
    }

    public static function objectName(): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', substr(static::class, strrpos(static::class, '\\') + 1)));
    }

    public static function howToExpand(string $propertyName): ?array
    {
        if (in_array($propertyName, static::$expandableProps)) {
            return [
                'target' => 'expandableProp',
                'object' => $propertyName,
            ];
        }

        return null;
    }

    public function toArray(): array
    {
        return $this->toArrayRecursive($this->props);
    }

    private function toArrayRecursive(array $inArray): array
    {
        $outArray = [];

        foreach ($inArray as $key => $value) {
            if ($value instanceof AbstractEntity || $value instanceof Collection) {
                $outArray[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $outArray[$key] = $this->toArrayRecursive($value);
            } else {
                $outArray[$key] = $value;
            }
        }

        return $outArray;
    }

    public function toString(): string
    {
        return json_encode($this->toArray());
    }

    public function getHttpStatusCode(): int
    {
        return 200;
    }
}
