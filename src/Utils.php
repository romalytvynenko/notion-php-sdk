<?php

namespace Notion;

use Illuminate\Support\Str;

class Utils
{
    const _NOTION_TO_MARKDOWN_MAPPER = [
        'i' => '☃',
        'b' => '☃☃',
        's' => '~~',
        'c' => '`',
    ];

    const delimiters = [
        '!',
        '"',
        '#',
        '$',
        '%',
        '&',
        '\'',
        '(',
        ')',
        '*',
        '+',
        ',',
        '-',
        '.',
        '/',
        ':',
        ';',
        '<',
        '=',
        '>',
        '?',
        '@',
        '[',
        '\\',
        ']',
        '^',
        '_',
        '`',
        '{',
        '|',
        '}',
        '~',
        '☃',
        ' ',
        '\t',
        '\n',
        '\x0b',
        '\x0c',
        '\r',
        '\x1c',
        '\x1d',
        '\x1e',
        '\x1f',
        '\x85',
        '\xa0',
        '\u1680',
        '\u2000',
        '\u2001',
        '\u2002',
        '\u2003',
        '\u2004',
        '\u2005',
        '\u2006',
        '\u2007',
        '\u2008',
        '\u2009',
        '\u200a',
        '\u2028',
        '\u2029',
        '\u202f',
        '\u205f',
        '\u3000',
    ];

    public static function signUrl(string $url)
    {
        if (Str::startsWith($url, getenv('S3_URL_PREFIX'))) {
            return getenv('SIGNED_URL_PREFIX').urlencode($url);
        }

        return $url;
    }

    public static function notionToMarkdown($notion)
    {
        $markdown_chunks = [];
        $use_underscores = true;

        foreach ($notion as $item) {
            $markdown = '';

            $text = $item[0];
            $format = $item[1] ?? [];

            $match = preg_match('/^(?P<leading>\\s*)(?P<stripped>(\\s|.)*?)(?P<trailing>\\s*)$/', $text, $matches);

            if (!$match) {
                throw new \Exception('Unable to extract text from: '.$text);
            }

            $leading_whitespace = $matches['leading'];
            $stripped = $matches['stripped'];
            $trailing_whitespace = $matches['trailing'];

            $markdown .= $leading_whitespace;

            //sorted_format = sorted(format, key=lambda x: FORMAT_PRECEDENCE.index(x[0]) if x[0] in FORMAT_PRECEDENCE else -1)
            $sorted_format = $format;

            foreach ($sorted_format as $f) {
                if (array_key_exists($f[0], self::_NOTION_TO_MARKDOWN_MAPPER)) {
                    if ($stripped) {
                        $markdown .= self::_NOTION_TO_MARKDOWN_MAPPER[$f[0]];
                    }
                }
                if ($f[0] === 'a') {
                    $markdown .= '[';
                }
            }

            $markdown .= $stripped;

            foreach (array_reverse($sorted_format) as $f) {
                if (array_key_exists($f[0], self::_NOTION_TO_MARKDOWN_MAPPER)) {
                    if ($stripped) {
                        $markdown .= self::_NOTION_TO_MARKDOWN_MAPPER[$f[0]];
                    }
                }
                if ($f[0] === 'a') {
                    $markdown .= sprintf('](%s)', $f[1]);
                }
            }

            $markdown .= $trailing_whitespace;

            // To make it parseable, add a space after if it combines code/links and emphasis formatting
            $format_types = array_map(function ($f) {
                return $f[0];
            }, $format);
            if (
                (in_array('c', $format_types, true) || in_array('a', $format_types, true)) &&
                (in_array('b', $format_types, true) || in_array('i', $format_types, true)) &&
                empty($trailing_whitespace)
            ) {
                $markdown .= ' ';
            }

            $markdown_chunks[] = $markdown;
        }

        $full_markdown = '';
        $last_used_underscores = false;
        foreach ($markdown_chunks as $i => $chunk) {
            $prev = $markdown_chunks[$i - 1] ?? '';
            $curr = $chunk;
            $next = $markdown_chunks[$i + 1] ?? '';

            $prev_ended_in_delimiter = in_array(mb_substr($prev, -1), self::delimiters, true);
            $next_starts_with_delimiter = in_array(mb_substr($next, 0), self::delimiters, true);

            if (
                $prev_ended_in_delimiter &&
                $next_starts_with_delimiter &&
                !$last_used_underscores &&
                mb_substr($curr, 0, 1) === '☃' &&
                mb_substr($curr, -1, 1) === '☃'
            ) {
                if (mb_substr($curr, 1, 1) === '☃') {
                    $count = 2;
                } else {
                    $count = 1;
                }

                $curr = str_pad(mb_substr($curr, $count, mb_strlen($curr) - $count * 2), $count, '_', STR_PAD_BOTH);
                $last_used_underscores = true;
            } else {
                $last_used_underscores = false;
            }

            $final_markdown = str_replace('☃', '*', $curr);

            // To make it parseable, convert emphasis/strong combinations to use a mix of _ and *
            if (mb_strpos($final_markdown, '***') !== false) {
                $final_markdown = preg_replace('/\*\*\*/', '**_', $final_markdown, 1);
                $final_markdown = preg_replace('/\*\*\*/', '_**', $final_markdown, 1);
            }

            $full_markdown .= $final_markdown;
        }

        return $full_markdown;
    }
}
