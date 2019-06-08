<?php

namespace Notion;

trait ClientAware
{
    /**
     * @var NotionClient
     */
    protected $client;

    public function getClient(): NotionClient
    {
        return $this->client;
    }

    public function setClient(NotionClient $client): self
    {
        $this->client = $client;

        return $this;
    }
}
