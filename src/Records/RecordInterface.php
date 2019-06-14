<?php

namespace Notion\Records;

use Ramsey\Uuid\UuidInterface;

interface RecordInterface
{
    public function getId(): UuidInterface;

    public function getUrl(): string;
}
