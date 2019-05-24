<?php

namespace Notion\Blocks;

class CollectionViewBlock extends BasicBlock
{
    public function getCollection(): CollectionBlock
    {
        return $this->getClient()->getCollection($this->get('collection_id'));
    }
}
