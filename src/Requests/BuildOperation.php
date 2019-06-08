<?php

namespace Notion\Requests;

use Illuminate\Contracts\Support\Arrayable;
use Ramsey\Uuid\UuidInterface;

class BuildOperation implements Arrayable
{
    /**
     * @var UuidInterface
     */
    protected $id;

    /**
     * @var array
     */
    protected $path;
    protected $args;
    protected $command;
    protected $table;

    public function __construct(UuidInterface $id, $path, $args, $command, $table)
    {
        $this->id = $id;
        $this->path = is_string($path) ? explode('.', $path) : $path;
        $this->args = $args;
        $this->command = $command;
        $this->table = $table;
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'path' => $this->path,
            'args' => $this->args,
            'command' => $this->command,
            'table' => $this->table,
        ];
    }
}
