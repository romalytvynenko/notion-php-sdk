<?php

namespace Notion\Records\Blocks;

use Notion\Utils;

class ImageBlock extends BasicBlock
{
    const BLOCK_TYPE = 'image';

    public function toString()
    {
        $url = $this->getProperty('source')->getValue();

        return sprintf('![](%s)', Utils::signUrl($url));
    }
}
