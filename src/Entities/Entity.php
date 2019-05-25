<?php

namespace Notion\Entities;

use Illuminate\Support\Arr;
use Notion\NotionClient;
use Ramsey\Uuid\UuidInterface;

class Entity
{
    /**
     * @var UuidInterface
     */
    protected $id;

    /**
     * @var array
     */
    protected $recordMap;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var NotionClient
     */
    protected $client;

    public function __construct(UuidInterface $id, $recordMap)
    {
        $this->id = $id;
        $this->recordMap = $recordMap;
        $this->attributes = $recordMap;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value): void
    {
        $this->set($name, $value);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getRecordMap(): array
    {
        return $this->recordMap;
    }

    public function setRecordMap(array $recordMap): void
    {
        $this->recordMap = $recordMap;
    }

    public function getClient(): NotionClient
    {
        return $this->client;
    }

    public function setClient(NotionClient $client): void
    {
        $this->client = $client;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function get(string $key)
    {
        return Arr::get($this->attributes, $key);
    }

    public function set(string $key, $value): void
    {
        Arr::set($this->attributes, $key, $value);
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}
