<?php

namespace Notion\Entities\Blocks;

class CollectionRowBlock extends PageBlock
{
    public function getTitle(): string
    {
        return $this->getProperty('Name');
    }
}
