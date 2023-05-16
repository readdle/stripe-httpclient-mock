<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock;

use Exception;
use Readdle\StripeHttpClientMock\Entity\AbstractEntity;
use Readdle\StripeHttpClientMock\Error\ResourceMissing;
use Readdle\StripeHttpClientMock\Success\Deleted;

final class EntityManager
{
    private static array $entities = [
        'card'           => [],
        'charge'         => [],
        'coupon'         => [],
        'customer'       => [],
        'discount'       => [],
        'invoice'        => [],
        'mandate'        => [],
        'payment_intent' => [],
        'payment_method' => [],
        'plan'           => [],
        'price'          => [],
        'product'        => [],
        'promotion_code' => [],
        'setup_intent'   => [],
        'source'         => [],
        'subscription'   => [],
        'tax_id'         => [],
        'tax_rate'       => [],

    ];

    /**
     * @throws Exception
     * @noinspection PhpUnused
     */
    final public static function loadFixtures(array $metadata): void
    {
        foreach ($metadata as $entityName => $fixturesFile) {
            $set = json_decode(file_get_contents($fixturesFile), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Fixtures file for entity '$entityName' contains invalid JSON");
            }

            $entityClass = self::resolveEntityClass($entityName);

            foreach ($set as $item) {
                self::$entities[$entityName][$item['id']] = call_user_func([$entityClass, 'create'], $item['id'], $item);
            }
        }
    }

    /**
     * @throws Exception
     */
    final public static function handleAction(string $action, string $entityName, ?string $entityId, array $params = []): ResponseInterface
    {
        switch ($action) {
            case 'create':
                return self::createEntity($entityName, $params);

            case 'list':
                return self::listEntity($entityName, $params);
        }

        if ($entityId !== null) {
            switch ($action) {
                case 'update':
                    return self::updateEntity($entityName, $entityId, $params);

                case 'retrieve':
                    return self::retrieveEntity($entityName, $entityId, $params);

                case 'delete':
                    return self::deleteEntity($entityName, $entityId);
            }
        }

        return self::subActionOn($action, $entityName, $entityId, $params);
    }

    /**
     * @throws Exception
     */
    public static function createEntity(string $entityName, array $params): ResponseInterface
    {
        $entityClass = self::resolveEntityClass($entityName);

        if (!array_key_exists($entityName, self::$entities)) {
            self::$entities[$entityName] = [];
        }

        if (!array_key_exists('id', $params)) {
            $prefix = call_user_func([$entityClass, 'prefix']);

            if (!empty($prefix)) {
                $prefix .= '_';
            }

            do {
                $id = uniqid($prefix);
            } while (array_key_exists($id, self::$entities[$entityName]));
        } else {
            $id = $params['id'];
        }

        return self::$entities[$entityName][$id] = call_user_func([$entityClass, 'create'], $id, $params);
    }

    public static function updateEntity(string $entityName, string $entityId, array $params): ResponseInterface
    {
        if (!array_key_exists($entityName, self::$entities) || !array_key_exists($entityId, self::$entities[$entityName])) {
            return new ResourceMissing();
        }

        return self::$entities[$entityName][$entityId]->update($params);
    }

    public static function listEntity(string $entityName, array $filters): ResponseInterface
    {
        if (array_key_exists('expand', $filters)) {
            $whatToExpand = $filters['expand'];
            unset($filters['expand']);
        } else {
            $whatToExpand = [];
        }

        foreach ($filters as $filter => $value) {
            if (
                array_key_exists($filter, self::$entities)
                && !empty($value)
                && !array_key_exists($value, self::$entities[$filter])
            ) {
                return new ResourceMissing("No such $filter: '$value'", $filter);
            }
        }

        $filtered = self::filter($entityName, $filters);
        $collection = new Collection($filtered['data'], $filtered['hasMore'], "/v1/{$entityName}s");

        if ($whatToExpand) {
            return self::expandCollection($whatToExpand, $collection);
        }

        return $collection;
    }

    public static function retrieveEntity(string $entityName, string $entityId, array $params = []): ResponseInterface
    {
        if (array_key_exists('expand', $params)) {
            $whatToExpand = $params['expand'];
            unset($params['expand']);
        } else {
            $whatToExpand = [];
        }

        if (
            !array_key_exists($entityName, self::$entities)
            || !array_key_exists($entityId, self::$entities[$entityName])
        ) {
            return new ResourceMissing();
        }

        if ($whatToExpand) {
            return self::expand($whatToExpand, self::$entities[$entityName][$entityId]);
        }

        return self::$entities[$entityName][$entityId];
    }

    public static function deleteEntity(string $entityName, string $entityId): ResponseInterface
    {
        if (
            !array_key_exists($entityName, self::$entities)
            || !array_key_exists($entityId, self::$entities[$entityName])
        ) {
            return new ResourceMissing();
        }

        unset(self::$entities[$entityName][$entityId]);
        $deleted = new Deleted();
        $deleted->id = $entityId;
        $deleted->object = $entityName;
        return $deleted;
    }

    /**
     * @throws Exception
     */
    private static function subActionOn(string $action, string $entityName, ?string $entityId, array $params): ResponseInterface
    {
        if (!array_key_exists($entityName, self::$entities) && !self::resolveEntityClass($entityName)) {
            return new ResourceMissing();
        }

        if ($entityId) {
            if (!array_key_exists($entityId, self::$entities[$entityName])) {
                return new ResourceMissing();
            }

            $entity = self::$entities[$entityName][$entityId];
        } else {
            $entity = self::createEntity($entityName, $params);
        }

        try {
            /** @var AbstractEntity $entity */
            return $entity->subAction($action, $params);
        } catch (Exception) {
            throw new Exception("Entity '$entityName' does not support action '$action'");
        }
    }

    /**
     * @throws Exception
     */
    public static function resolveEntityClass(string $entityName): string
    {
        /** @noinspection SpellCheckingInspection */
        $exceptions = [
            'invoiceitem' => 'InvoiceItem',
        ];

        $shortClass = array_key_exists($entityName, $exceptions)
            ? $exceptions[$entityName]
            : join('', array_map('ucfirst', preg_split('/[._]/', $entityName)));

        $entityClass = __NAMESPACE__ . '\\Entity\\' . $shortClass;

        if (!class_exists($entityClass)) {
            throw new Exception("Unknown entity $entityClass");
        }

        if (!is_subclass_of($entityClass, AbstractEntity::class)) {
            throw new Exception("$entityClass does not extend Entity class");
        }

        return $entityClass;
    }

    private static function filter(string $entityName, array $filters): array
    {
        $predefined = [
            'limit'          => 10,
            'lookup_keys'    => null,
            'starting_after' => null,
        ];

        foreach ($predefined as $key => $value) {
            if (array_key_exists($key, $filters)) {
                $predefined[$key] = $filters[$key];
                unset($filters[$key]);
            }
        }

        extract($predefined);

        $resultSet = [];
        $startingAfterFound = false;
        $currentLastKey = '';

        if (!array_key_exists($entityName, self::$entities)) {
            self::$entities[$entityName] = [];
        }

        foreach (self::$entities[$entityName] as $entity) {
            if ($lookup_keys !== null && !in_array($entity->lookup_key, $lookup_keys)) {
                continue;
            }

            if ($starting_after !== null && !$startingAfterFound) {
                if ($entity->id !== $starting_after) {
                    continue;
                }

                $startingAfterFound = true;
                continue;
            }

            foreach ($filters as $key => $value) {
                if ($entity->$key !== $value) {
                    if (
                        gettype($entity->$key) !== 'boolean'
                        || (
                            ($entity->$key === false && $value !== 'false')
                            || ($entity->$key === true && $value !== 'true')
                        )
                    ) {
                        continue 2;
                    }
                }
            }

            $resultSet[] = $entity;
            $currentLastKey = $entity->id;

            if (count($resultSet) === $limit) {
                break;
            }
        }

        if (count($resultSet) < $limit) {
            $hasMore = false;
        } else {
            $hasMore = array_key_last(self::$entities[$entityName]) !== $currentLastKey;
        }

        return [
            'data'    => $resultSet,
            'hasMore' => $hasMore,
        ];
    }

    private static function expand(array $whatToExpand, AbstractEntity $entity): ResponseInterface
    {
        $clone = clone $entity;

        foreach ($whatToExpand as $target) {
            $path = explode('.', $target);
            $pointer = $clone;

            foreach ($path as $prop) {
                $value = $pointer->$prop;

                if (empty($value)) {
                    continue 2;
                }

                $howToExpand = call_user_func([$pointer, 'howToExpand'], $prop);

                if ($howToExpand === null) {
                    continue 2;
                }

                switch ($howToExpand['target']) {
                    case 'objectSearcher':
                        $expandedEntity = $howToExpand['searcher']($clone, self::$entities[$howToExpand['object']]);
                        break;

                    case 'expandableProp':
                        if ($value instanceof AbstractEntity) {
                            // already expanded
                            $expandedEntity = $value;
                        } else {
                            $expandedEntity = self::retrieveEntity(
                                $howToExpand['object'],
                                $value,
                                $howToExpand['params'] ?? []
                            );
                        }
                        break;

                    default:
                        continue 2;
                }


                if (!$expandedEntity instanceof AbstractEntity) {
                    continue 2;
                }

                $pointer->$prop = $expandedEntity;
                $pointer = $pointer->$prop;
            }
        }

        return $clone;
    }

    public static function expandCollection(array $whatToExpand, Collection $collection): Collection
    {
        $whatToExpand = array_map(
            fn ($whatToExpand) => preg_replace('/^data\./', '', $whatToExpand),
            $whatToExpand
        );

        $collection->data = array_map(
            fn ($entity) => self::expand($whatToExpand, $entity),
            $collection->data
        );

        return $collection;
    }
}
