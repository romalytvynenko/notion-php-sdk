<?php

namespace Notion\Blocks;

use Illuminate\Support\Arr;
use Notion\Entity;
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
            'collection' => CollectionBlock::class,
            'collection_view' => CollectionViewBlock::class,
        ];

        $blockType = $types[$this->get('type')] ?? null;

        return $blockType
            ? new $blockType($this->getId(), $this->getRecordMap())
            : $this;
    }

    public function getTitle(): string
    {
        return $this->getTextAttribute('properties.title');
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

    protected function getTextAttribute(string $propertyName): string
    {
        $property = $this->get($propertyName) ?? [];

        return Arr::first(Arr::flatten($property)) ?? '';
    }
}
