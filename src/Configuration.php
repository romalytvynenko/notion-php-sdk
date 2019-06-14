<?php

namespace Notion;

class Configuration
{
    /**
     * @var string
     */
    private $baseUrl = 'https://www.notion.so/';

    /**
     * @var string
     */
    private $apiBaseUrl = 'https://www.notion.so/api/v3/';

    /**
     * @var string
     */
    private $token;

    /**
     * @var int
     */
    private $cacheLifetime = 24 * 60;

    public function getApiBaseUrl(): string
    {
        return $this->apiBaseUrl;
    }

    public function setApiBaseUrl(string $apiBaseUrl): void
    {
        $this->apiBaseUrl = $apiBaseUrl;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getCacheLifetime(): int
    {
        return $this->cacheLifetime;
    }

    public function setCacheLifetime(int $cacheLifetime): void
    {
        $this->cacheLifetime = $cacheLifetime;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }
}
