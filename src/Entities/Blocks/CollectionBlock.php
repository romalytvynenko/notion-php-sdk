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

        return collect($pages['block'])
            ->keys()
            ->map(function ($id) use ($pages) {
                $block = (new BasicBlock(Identifier::fromString($id), $pages))->toTypedBlock();

                $block->setClient($this->getClient());
                $block->createPropertiesFromSchemas($this->get('schema'));

                return $block;
            })
            ->filter(function (BlockInterface $block) {
                return $block instanceof CollectionRowBlock;
            });
    }
}
