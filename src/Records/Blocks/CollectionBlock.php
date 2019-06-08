<?php

namespace Notion\Records\Blocks;

use Illuminate\Support\Collection;
use Notion\Records\Identifier;

class CollectionBlock extends BasicBlock
{
    public function getTitle(): string
    {
        return $this->getTextAttribute('name') ?: parent::getTitle();
    }

    public function getTable(): string
    {
        return 'collection';
    }

    public function addRow(array $attributes): CollectionRowBlock
    {
        $blockId = $this->getClient()->createRecord('block', $this, ['type' => PageBlock::BLOCK_TYPE]);
        $block = $this->getClient()->getBlock($blockId->toString());
        foreach ($attributes as $key => $value) {
            $block->setProperty($key, $value);
        }

        return $block;
    }

    public function getRow($uuid): ?CollectionRowBlock
    {
        $uuid = Identifier::fromString($uuid);

        return $this->getRows()->first(function (CollectionRowBlock $row) use ($uuid) {
            return $row->getId()->toString() === $uuid->toString();
        });
    }

    /**
     * @return Collection|CollectionRowBlock[]
     */
    public function getRows(string $query = ''): Collection
    {
        $pages = $this->getClient()->getByParent($this->getId(), $query);
        $children = collect($pages['block'])
            ->keys()
            ->map(function ($id) use ($pages) {
                return (new BasicBlock(Identifier::fromString($id), $pages))->toTypedBlock();
            });

        return $this->toChildBlocks($children)->filter(function (BlockInterface $block) {
            return $block instanceof CollectionRowBlock;
        });
    }
}
