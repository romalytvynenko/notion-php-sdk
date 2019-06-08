<?php

namespace Notion\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Ramsey\Uuid\UuidInterface;

class RecordRequest implements Arrayable
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var UuidInterface
     */
    private $id;

    public function __construct(string $table, UuidInterface $id)
    {
        $this->table = $table;
        $this->id = $id;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function toArray(): array
    {
        return ['table' => $this->table, 'id' => $this->id];
    }
}
