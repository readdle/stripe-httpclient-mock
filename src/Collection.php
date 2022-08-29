<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock;

use Readdle\StripeHttpClientMock\Entity\AbstractEntity;

class Collection implements ResponseInterface
{
    public string $object = 'list';
    public array $data = [];
    public bool $hasMore = false;
    public string $url = '';

    public function __construct(array $data = [], bool $hasMore = false, string $url = '')
    {
        $this->data = $data;
        $this->hasMore = $hasMore;
        $this->url = $url;
    }

    public function add(AbstractEntity $entity): void
    {
        $this->data[] = $entity;
    }

    public function addCollection(Collection $collection): void
    {
        $this->data = array_merge($this->data, $collection->data);
    }

    public function toArray(): array
    {
        return [
            'object' => $this->object,
            'data' => array_map(fn($entity) => $entity->toArray(), $this->data),
            'has_more' => $this->hasMore,
            'url' => $this->url,
        ];
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
