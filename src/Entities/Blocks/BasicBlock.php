<?php

namespace Notion\Entities\Blocks;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Notion\Entities\Property;
use Notion\Entities\Record;
use Ramsey\Uuid\UuidInterface;

/**
 * @property string id
 * @property string title
 * @property string description
 */
class BasicBlock extends Record implements BlockInterface
{
    public const BLOCK_TYPE = 'block';

    /**
     * @var BlockInterface
     */
    protected $parent;

    /**
     * @var Collection|Property[]
     */
    protected $properties;

    public function __construct(UuidInterface $id, $recordMap)
    {
        parent::__construct($id, $recordMap);
        $this->attributes = Arr::get($this->recordMap, 'block.'.$this->id.'.value');
    }

    public function __get($name)
    {
        switch ($name) {
            case 'id':
                return $this->getId()->toString();

            case 'description':
                return $this->getDescription();

            case 'title':
                return $this->getTitle();

            default:
                $property = $this->getProperty($name);

                return $property ? $property->getValue() : null;
        }
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
        return (string) $this->getProperty('title');
    }

    public function getTable(): string
    {
        return 'block';
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

    public function createProperties(array $schemas = []): void
    {
        if ($schemas) {
            $this->setProperties(
                collect($schemas)->mapWithKeys(function ($schema, $hash) {
                    $property = $this->unwrapValue($this->get('properties.'.$hash) ?? []);
                    $name = $schema['name'] ?? '';

                    return [$name => new Property($schema, $property)];
                })
            );
        } else {
            $this->setProperties(
                collect($this->get('properties'))->map(function ($property) {
                    return new Property([], $this->unwrapValue($property));
                })
            );
        }
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

    public function getChildren(): Collection
    {
        return $this->toChildBlocks(
            collect($this->get('content'))->map(function (string $id) {
                return $this->client->getBlock($id);
            })
        );
    }

    public function getContents()
    {
        return $this->getChildren()
            ->map(function (BasicBlock $block) {
                return $block->getTitle();
            })
            ->join(' ');
    }

    protected function toChildBlocks(Collection $blocks): Collection
    {
        return collect($blocks)->map(function (BasicBlock $block) {
            $block->setClient($this->getClient());
            $block->createProperties($this->get('schema') ?? []);

            return $block;
        });
    }
}
