<?php

namespace Notion\Blocks;

use Notion\Identifier;

class CollectionBlock extends BasicBlock
{
    public function getTitle(): string
    {
        return $this->getTextAttribute('name') ?: parent::getTitle();
    }

    /**
     * @return BlockInterface[]
     */
    public function getChildren(): array
    {
        $pages = $this->getClient()->getByParent($this->getId());
        $blocks = collect($pages['block'])->keys()->map(function ($id) use ($pages) {
           return new BasicBlock(Identifier::fromString($id), $pages);
        });

        return $blocks->toArray();
    }
}
