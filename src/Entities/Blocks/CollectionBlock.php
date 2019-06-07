<?php

namespace Notion\Entities\Blocks;

use Illuminate\Support\Collection;
use Notion\Entities\Identifier;

class CollectionBlock extends BasicBlock
{
    public function getTitle(): string
    {
        return $this->getTextAttribute('name') ?: parent::getTitle();
    }

    /**
     * @return CollectionRowBlock[]
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
