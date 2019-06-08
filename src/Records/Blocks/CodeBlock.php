<?php

namespace Notion\Records\Blocks;

class CodeBlock extends BasicBlock
{
    const BLOCK_TYPE = 'code';

    public function toString()
    {
        return '```'.parent::toString().'```'.PHP_EOL;
    }
}
