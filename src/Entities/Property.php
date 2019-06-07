<?php

namespace Notion\Entities;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Property
{
    /**
     * @var array
     */
    private $schema;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(array $schema, $value)
    {
        $this->schema = $schema;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->getSchema()['name'] ?? '';
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function setSchema(array $schema): void
    {
        $this->schema = $schema;
    }

    public function getValue()
    {
        $type = $this->schema['type'] ?? '';
        switch ($type) {
            case 'multi_select':
            case 'select':
            case 'text':
                return trim($this->value);
            default:
                return $this->value;
        }
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->getValue();
    }

    protected function getOptions(): Collection
    {
        return collect($this->schema['options'] ?? []);
    }

    public function getOption()
    {
        return $this->getOptions()->first(function ($option) {
            return $option['value'] === $this->getValue();
        });
    }

    public function getOptionAttribute(string $key)
    {
        return Arr::get($this->getOption(), $key);
    }
}
