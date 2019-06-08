<?php

namespace Notion\Records\Blocks;

class SubHeaderBlock extends BasicBlock
{
    const BLOCK_TYPE = 'sub_header';

    public function toString()
    {
        return '## '.parent::toString();
    }
}
