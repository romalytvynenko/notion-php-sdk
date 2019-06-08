<?php

namespace Notion\Entities\Blocks;

use Ramsey\Uuid\UuidInterface;

interface BlockInterface
{
    public function getId(): UuidInterface;

    public function getTable(): string;

    public function getParent(): ?BlockInterface;

    public function getCollection(): ?CollectionBlock;
}
