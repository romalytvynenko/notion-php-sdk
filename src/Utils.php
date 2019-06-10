<?php

namespace Notion;

use Illuminate\Support\Str;

class Utils
{
    public static function signUrl(string $url)
    {
        if (Str::startsWith($url, getenv('S3_URL_PREFIX'))) {
            return getenv('SIGNED_URL_PREFIX').urlencode($url);
        }

        return $url;
    }
}
