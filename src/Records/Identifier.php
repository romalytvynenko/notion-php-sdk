<?php

namespace Notion\Records;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Identifier extends Uuid
{
    public static function fromString(string $uuid): UuidInterface
    {
        return Str::contains($uuid, 'http') ? static::fromUrl($uuid) : parent::fromString($uuid);
    }

    public static function fromUrl(string $url): UuidInterface
    {
        $identifier = $url;
        foreach (['#', '/', '&p=', '?', '-'] as $delimiter) {
            $pieces = explode($delimiter, $identifier);
            $identifier = $delimiter === '?' ? Arr::first($pieces) : Arr::last($pieces);
        }

        return static::fromString($identifier);
    }
}
