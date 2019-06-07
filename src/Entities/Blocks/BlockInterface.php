<?php

namespace Notion\Entities\Blocks;

interface BlockInterface
{
    public function getParent(): ?BlockInterface;

    public function getCollection(): ?CollectionBlock;
}
