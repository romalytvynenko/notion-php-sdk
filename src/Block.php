<?php

namespace Notion;

use Illuminate\Support\Arr;
use Ramsey\Uuid\UuidInterface;

class Block
{
    /**
     * @var UuidInterface
     */
    private $id;

    /**
     * @var array
     */
    private $recordMap;

    /**
     * @var array
     */
    private $attributes;

    public function __construct(UuidInterface $id, $attributes)
    {
        $this->id = $id;
        $this->recordMap = $attributes;
        $this->attributes = Arr::get(
            $this->recordMap,
            'block.'.$this->id->toString().'.value'
        );
    }

    public function getTitle(): string
    {
        return Arr::first(
            Arr::flatten($this->getAttribute('properties.title'))
        );
    }

    public function getRecordMap(): array
    {
        return $this->recordMap;
    }

    public function setRecordMap(array $recordMap): void
    {
        $this->recordMap = $recordMap;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): void
    {
        $this->id = $id;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $key)
    {
        return Arr::get($this->attributes, $key);
    }

    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }
}
