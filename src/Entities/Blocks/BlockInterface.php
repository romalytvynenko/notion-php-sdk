<?php

namespace Notion\Entities\Blocks;

interface BlockInterface
{
    public function getParent(): ?BlockInterface;
}
