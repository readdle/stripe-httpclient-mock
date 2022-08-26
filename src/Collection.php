<?php
declare(strict_types=1);

namespace Readdle\StripeHttpClientMock;

use Readdle\StripeHttpClientMock\Entity\AbstractEntity;

class Collection
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

    public function add(AbstractEntity $entity)
    {
        $this->data[] = $entity;
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
}
