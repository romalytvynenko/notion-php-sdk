<?php

namespace Notion\Records\Blocks;

class QuoteBlock extends BasicBlock
{
    const BLOCK_TYPE = 'quote';

    public function toString()
    {
        return '> '.parent::toString();
    }
}
