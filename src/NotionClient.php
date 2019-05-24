<?php

namespace Notion;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;

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
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
        $this->client = new Client([
            'base_uri' => getenv('API_BASE_URL')
        ]);
    }

    public function getBlock(string $identifier)
    {
        $blockId = $this->extractIdentifier($identifier);
        $block = $this->fetchBlock($blockId);
        $block = new Block($block);

        dd($block, 'lol');
        /*
        if not block:
            return None
        if block.get("parent_table") == "collection":
            if block.get("is_template"):
                block_class = TemplateBlock
            else:
                block_class = CollectionRowBlock
        else:
            block_class = BLOCK_TYPES.get(block.get("type", ""), Block)
        return block_class(self, $blockId)*/
    }

    private function fetchBlock(string $blockId)
    {
        $response = $this->client->post('loadPageChunk', [
            'headers' => [
                'Cookie' => 'token_v2=' . getenv('NOTION_TOKEN'),
                'Content-Type' => 'application/json; charset=utf-8'
            ],
            'body' => json_encode([
                'pageId' => $blockId,
                'limit' => 50,
                'cursor' => ['stack' => []],
                'chunkNumber' => 0,
                'verticalColumns' => false
            ])
        ]);

        $response = $response->getBody()->getContents();
        $response = json_decode($response, true);

        return $response['recordMap'] ?? [];
    }

    private function extractIdentifier(string $identifier)
    {
        [, $identifier] = explode('-', $identifier);

        return $identifier;
    }
}
