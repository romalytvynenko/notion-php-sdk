<?php

namespace Notion\Entities\Blocks;

class CollectionViewBlock extends BasicBlock
{
    public const BLOCK_TYPE = 'collection_view';

    public function getTable(): string
    {
        return 'collection_view';
    }

    public function getCollection(): CollectionBlock
    {
        return $this->getClient()->getCollection($this->get('collection_id'));
    }

    public function getRows()
    {
        return $this->getCollection()->getRows();
    }
}
