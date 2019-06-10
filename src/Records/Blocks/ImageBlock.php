<?php

namespace Notion\Records\Blocks;

use Notion\Records\Url;

class ImageBlock extends BasicBlock
{
    const BLOCK_TYPE = 'image';

    public function toString()
    {
        $url = $this->getProperty('source')->getValue();
        $url = (new Url($url))->toSignedUrl();

        return sprintf('![](%s)', $url);
    }
}
