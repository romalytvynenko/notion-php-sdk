<?php

namespace Notion;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Cache\CacheItemInterface;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NotionClient
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var Client
     */
    protected $client;
    /**
     * @var CacheInterface
     */
    protected $cache;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->cache = new FilesystemAdapter();
        $this->client = new Client([
            'base_uri' => getenv('API_BASE_URL'),
        ]);
    }

    public function getBlock(string $identifier): Block
    {
        $blockId = $this->extractIdentifier($identifier);
        $blockAttributes = $this->fetchBlock($blockId);

        return new Block($blockId, $blockAttributes);
    }

    private function fetchBlock(UuidInterface $blockId): array
    {
        $response = $this->cache->get(
            'block-'.$blockId->toString(),
            function (CacheItemInterface $item) use ($blockId) {
                $response = $this->client->post('loadPageChunk', [
                    'cookies' => CookieJar::fromArray(
                        [
                            'token_v2' => getenv('NOTION_TOKEN'),
                        ],
                        'www.notion.so'
                    ),
                    'headers' => [
                        'Content-Type' => 'application/json; charset=utf-8',
                    ],
                    'body' => json_encode([
                        'pageId' => $blockId->toString(),
                        'limit' => 50,
                        'cursor' => ['stack' => []],
                        'chunkNumber' => 0,
                        'verticalColumns' => false,
                    ]),
                ]);

                $response = $response->getBody()->getContents();

                return json_decode($response, true);
            }
        );

        return $response['recordMap'] ?? [];
    }

    private function extractIdentifier(string $identifier): UuidInterface
    {
        [, $identifier] = explode('-', $identifier);

        return Uuid::fromString($identifier);
    }
}
