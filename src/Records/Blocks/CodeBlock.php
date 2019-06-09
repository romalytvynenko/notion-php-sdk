<?php

namespace Notion\Records\Blocks;

class CodeBlock extends BasicBlock
{
    const BLOCK_TYPE = 'code';

    public function toString()
    {
        $language = mb_strtolower($this->getProperty('language')->getValue());

        return '```'.$language.PHP_EOL.trim(parent::toString()).PHP_EOL.'```'.PHP_EOL;
    }
}
