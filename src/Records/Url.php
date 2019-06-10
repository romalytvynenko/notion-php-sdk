<?php

namespace Notion\Records;

use Illuminate\Support\Str;

class Url
{
    /**
     * @var string
     */
    protected $url;

    /**
     * Url constructor.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function toSignedUrl(): string
    {
        if (Str::startsWith($this->url, getenv('S3_URL_PREFIX'))) {
            return getenv('SIGNED_URL_PREFIX').urlencode($this->url);
        }

        return $this->url;
    }
}
