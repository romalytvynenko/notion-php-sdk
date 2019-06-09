<?php

namespace Notion\Records\Blocks;

class SubsubHeaderBlock extends BasicBlock
{
    const BLOCK_TYPE = 'sub_sub_header';

    public function toString()
    {
        return '### '.parent::toString();
    }
}
