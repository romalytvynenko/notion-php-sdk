<?php

namespace Notion\Records\Blocks;

use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface BlockInterface
{
    public function getId(): UuidInterface;

    public function getTitle(): string;

    public function getTable(): string;

    public function getProperties(): Collection;

    public function setProperties(Collection $properties): void;

    public function getProperty(string $needle);

    public function setProperty(string $key, $value);

    public function getParent(): ?BlockInterface;

    public function getCollection(): ?CollectionBlock;
}
