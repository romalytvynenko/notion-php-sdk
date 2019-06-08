<?php

namespace Notion\Records;

use DateTime;
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
        return $this->getSchema()['name'] ?? null;
    }

    public function getHash()
    {
        return $this->getSchema()['hash'] ?? null;
    }

    public function getPath(): array
    {
        return ['properties', $this->getHash() ?? $this->getName()];
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    public function getRawValue()
    {
        return $this->value;
    }

    public function getValue()
    {
        switch ($this->getType()) {
            case 'checkbox':
                return $this->value === 'Yes';

            case 'date':
                return new DateTime($this->value);

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
        switch ($this->getType()) {
            case 'checkbox':
                $this->value = $value ? 'Yes' : '';
                break;
            default:
                $this->value = $value;
                break;
        }
    }

    public function __toString()
    {
        return (string) $this->getValue();
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

    /**
     * @return mixed|string
     */
    public function getType()
    {
        return $this->schema['type'] ?? 'text';
    }
}
