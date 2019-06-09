<?php

namespace Notion\Records\Blocks;

class ImageBlock extends BasicBlock
{
    const BLOCK_TYPE = 'image';

    public function toString()
    {
        return sprintf('![](%s)', $this->getProperty('source')->getValue());
    }
}
