<?php

namespace Notion\Entities\Blocks;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Notion\Entities\Entity;
use Notion\Entities\Property;
use Ramsey\Uuid\UuidInterface;

class BasicBlock extends Entity implements BlockInterface
{
    /**
     * @var Collection|Property[]
     */
    protected $properties;

    public function __construct(UuidInterface $id, $recordMap)
    {
        parent::__construct($id, $recordMap);
        $this->attributes = Arr::get($this->recordMap, 'block.'.$this->id->toString().'.value');
    }

    public function __get($name)
    {
        $property = $this->getProperty($name);

        return $property ? $property->getValue() : null;
    }

    public function __set($name, $value): void
    {
        $this->setProperty($name, $value);
    }

    public function toTypedBlock(): BasicBlock
    {
        $types = [
            'page' => PageBlock::class,
            'collection' => CollectionBlock::class,
            'collection_view' => CollectionViewBlock::class,
            'collection_view_page' => CollectionViewBlock::class,
        ];

        $blockType =
            $this->get('parent_table') === 'collection'
                ? CollectionRowBlock::class
                : $types[$this->get('type')] ?? null;
        $block = $blockType ? new $blockType($this->getId(), $this->getRecordMap()) : $this;

        if ($this->client) {
            $block->setClient($this->client);
        }

        return $block;
    }

    public function getTitle(): string
    {
        return $this->getProperty('title');
    }

    public function getIcon(): string
    {
        return $this->get('format.page_icon');
    }

    public function getDescription(): string
    {
        return $this->getTextAttribute('description');
    }

    public function getParent(): ?BlockInterface
    {
        $isAlias = false;
        if (!$isAlias) {
            $parentId = $this->get('parent_id');
            $parentTable = $this->get('parent_table');
        } else {
            $parentId = $this->_alias_parent;
            $parentTable = 'block';
        }

        switch ($parentTable) {
            case 'block':
                return $this->getClient()->getBlock($parentId);

            case 'collection':
                return $this->getClient()->getCollection($parentId);

            case 'space':
                return $this->getClient()->getSpace($parentId);

            default:
                return null;
        }
    }

    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function setProperties(Collection $properties): void
    {
        $this->properties = $properties;
    }

    public function createPropertiesFromSchemas(array $schemas): void
    {
        $this->setProperties(
            collect($schemas)->mapWithKeys(function ($schema, $hash) {
                $property = $this->unwrapValue($this->get('properties.'.$hash) ?? []);
                $name = $schema['name'] ?? '';

                return [$name => new Property($schema, $property)];
            })
        );
    }

    public function getProperty(string $needle)
    {
        return $this->properties->first(function (Property $property, $key) use ($needle) {
            return $key === $needle ||
                Str::slug($property->getName()) === $needle ||
                Str::snake($property->getName()) === $needle;
        });
    }

    public function setProperty(string $key, $value)
    {
        return $this->properties[$key] = $value;
    }

    protected function getTextAttribute(string $propertyName): string
    {
        $property = $this->get($propertyName) ?? [];

        return $this->unwrapValue($property);
    }

    /**
     * @return mixed|string
     */
    protected function unwrapValue(array $property)
    {
        return Arr::last(Arr::flatten($property)) ?? '';
    }

    public function getCollection(): ?CollectionBlock
    {
        return null;
    }
}
