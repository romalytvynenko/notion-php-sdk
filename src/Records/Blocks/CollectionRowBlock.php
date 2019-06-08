<?php

namespace Notion\Records\Blocks;

class CollectionRowBlock extends PageBlock
{
    public function getTitle(): string
    {
        return $this->getProperty('Name');
    }
}
