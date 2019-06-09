<?php

namespace Notion\Records\Blocks;

class NumberedListBlock extends BasicBlock
{
    const BLOCK_TYPE = 'numbered_list';

    public function toString()
    {
        return '- '.parent::toString();
    }
}
