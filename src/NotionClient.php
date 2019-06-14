<?php

namespace Notion;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Arr;
use Notion\Records\Blocks\BasicBlock;
use Notion\Records\Blocks\BlockInterface;
use Notion\Records\Blocks\CollectionBlock;
use Notion\Records\Identifier;
use Notion\Records\Record;
use Notion\Records\Space;
use Notion\Records\User;
use Notion\Requests\BuildOperation;
use Notion\Requests\RecordRequest;
use Psr\SimpleCache\CacheInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NotionClient
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Space
     */
    protected $currentSpace;

    /**
     * @var User
     */
    protected $currentUser;

    public function __construct(string $token, Configuration $config = null)
    {
        $this->configuration = $config ?? new Configuration();
        $this->configuration->setToken($token);

        $this->cache = new FilesystemAdapter('', $this->configuration->getCacheLifetime());
        $this->client = new Client([
            'base_uri' => $this->configuration->getApiBaseUrl(),
            'cookies' => CookieJar::fromArray(
                [
                    'token_v2' => $this->configuration->getToken(),
                ],
                'www.notion.so'
            ),
        ]);

        $this->loadUserInformations();
    }

    public function getBlock(string $identifier): BlockInterface
    {
        $blockId = Identifier::fromString($identifier);
        $attributes = $this->loadPageChunk($blockId);

        $block = (new BasicBlock($blockId, $attributes))->toTypedBlock();
        $block->setClient($this);

        if ($block->get('parent_table') === 'collection' && $block->get('properties')) {
            $schema = $block->getParent()->get('schema') ?? [];
            $block->createProperties($schema);
        }

        return $block;
    }

    public function getCollection(string $identifier): CollectionBlock
    {
        $collectionId = Identifier::fromString($identifier);
        $attributes = $this->getRecordValues(new RecordRequest('collection', $collectionId))['value'];

        $collection = new CollectionBlock($collectionId, []);
        $collection->setAttributes($attributes);
        $collection->setClient($this);

        return $collection;
    }

    private function loadPageChunk(UuidInterface $blockId): array
    {
        $response = $this->cachedJsonRequest('block-'.$blockId->toString(), 'loadPageChunk', [
            'pageId' => $blockId->toString(),
            'limit' => 50,
            'cursor' => ['stack' => []],
            'chunkNumber' => 0,
            'verticalColumns' => false,
        ]);

        return $response['recordMap'] ?? [];
    }

    private function getRecordValues(RecordRequest $request): ?array
    {
        return $this->getRecordsValues([$request])[$request->getId()->toString()] ?? null;
    }

    /**
     * @param RecordRequest[] $requests
     */
    private function getRecordsValues(array $requests): array
    {
        $requests = collect($requests);
        $response = $this->cachedJsonRequest(sha1($requests->toJson()), 'getRecordValues', [
            'requests' => $requests->toArray(),
        ]);

        $results = $requests->mapWithKeys(function (RecordRequest $request, $key) use ($response) {
            $id = $request->getId()->toString();

            return [$id => $response['results'][$key] ?? []];
        });

        return $results->toArray();
    }

    public function getByParent(UuidInterface $getId, string $query = '')
    {
        $response = $this->cachedJsonRequest('by-parent-'.$getId->toString(), 'searchPagesWithParent', [
            'query' => $query,
            'parentId' => $getId->toString(),
            'limit' => 10000,
            'spaceId' => $this->getCurrentSpace()
                ->getId()
                ->toString(),
        ]);

        return $response['recordMap'] ?? [];
    }

    public function getCurrentSpace(): Space
    {
        return $this->currentSpace;
    }

    public function getCurrentUser(): User
    {
        return $this->currentUser;
    }

    private function loadUserInformations(): void
    {
        $response = $this->cachedJsonRequest('user-informations', 'loadUserContent');
        $fromRecordMap = static function (string $class, string $key, array $response): Record {
            $record = $response['recordMap'][$key];
            $record = Arr::first($record)['value'];

            return new $class(Identifier::fromString($record['id']), $record);
        };

        $this->currentSpace = $fromRecordMap(Space::class, 'space', $response);
        $this->currentUser = $fromRecordMap(User::class, 'notion_user', $response);
    }

    private function cachedJsonRequest(string $key, string $url, array $body = [])
    {
        return $this->cache->get($key, function () use ($url, $body) {
            $options = $body ? ['json' => $body] : [];
            $response = $this->client->post($url, $options);
            $response = $response->getBody()->getContents();

            return json_decode($response, true);
        });
    }

    public function createRecord(
        string $table,
        BlockInterface $parent,
        array $attributes,
        array $children = []
    ): UuidInterface {
        $uuid = Uuid::uuid4();
        $operation = new BuildOperation(
            $uuid,
            [],
            array_merge(
                [
                    'id' => $uuid->toString(),
                    'version' => 1,
                    'alive' => true,
                    'created_by' => $this->getCurrentUser()
                        ->getId()
                        ->toString(),
                    'created_time' => time(),
                    'parent_id' => $parent->getId()->toString(),
                    'parent_table' => $parent->getTable(),
                ],
                $attributes
            ),
            'set',
            $table
        );

        $this->submitTransation([$operation]);

        return $uuid;
    }

    /**
     * @param BuildOperation[] $operations
     */
    public function submitTransation(array $operations): void
    {
        $operations = collect($operations);

        $this->client->post('submitTransaction', [
            'json' => ['operations' => $operations->toArray()],
        ]);
    }

    public function getSpace($spaceId): Space
    {
        $identifier = Identifier::fromString($spaceId);
        $space = $this->getRecordValues(new RecordRequest('space', $identifier));

        return new Space($identifier, $space);
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getUser($userId)
    {
        $identifier = Identifier::fromString($userId);
        $user = $this->getRecordValues(new RecordRequest('notion_user', $identifier));

        return new User($identifier, $user['value'] ?? []);
    }
}
