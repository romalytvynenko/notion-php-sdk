<?php

namespace Notion\Blocks;

interface BlockInterface
{
    public function getParent(): ?BlockInterface;
}
