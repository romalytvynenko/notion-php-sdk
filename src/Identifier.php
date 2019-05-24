<?php

namespace Notion;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Identifier extends Uuid
{
    public static function fromString($name)
    {
        return Str::contains($name, 'http') ? static::fromUrl($name) : parent::fromString($name);
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
