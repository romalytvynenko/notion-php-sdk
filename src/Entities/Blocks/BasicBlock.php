<?php

namespace Notion\Entities\Blocks;

use Illuminate\Support\Arr;
use Notion\Entities\Entity;
use Ramsey\Uuid\UuidInterface;

class BasicBlock extends Entity implements BlockInterface
{
    public function __construct(UuidInterface $id, $recordMap)
    {
        parent::__construct($id, $recordMap);
        $this->attributes = Arr::get(
            $this->recordMap,
            'block.'.$this->id->toString().'.value'
        );
    }

    public function toTypedBlock(): BasicBlock
    {
        $types = [
            'page' => PageBlock::class,
            'collection' => CollectionBlock::class,
            'collection_view' => CollectionViewBlock::class,
        ];

        $blockType =
            $this->get('parent_table') === 'collection'
                ? CollectionRowBlock::class
                : $types[$this->get('type')] ?? null;
        $block = $blockType
            ? new $blockType($this->getId(), $this->getRecordMap())
            : $this;

        if ($this->client) {
            $block->setClient($this->client);
        }

        return $block;
    }

    public function getTitle(): string
    {
        return $this->getProperty('title');
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

    public function getProperty(string $key)
    {
        return $this->getTextAttribute('properties.'.$key);
    }

    protected function getTextAttribute(string $propertyName): string
    {
        $property = $this->get($propertyName) ?? [];

        return Arr::first(Arr::flatten($property)) ?? '';
    }
}
