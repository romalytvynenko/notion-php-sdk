<?php

namespace Notion\Records\Blocks;

class HeaderBlock extends BasicBlock
{
    const BLOCK_TYPE = 'header';

    public function toString()
    {
        return '# '.parent::toString();
    }
}
