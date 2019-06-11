<?php

namespace Notion\Records\Blocks;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;
use Notion\Records\Property;
use Notion\Records\Record;
use Notion\Requests\BuildOperation;
use Notion\Utils;
use Ramsey\Uuid\UuidInterface;

/**
 * @property string id
 * @property string title
 * @property string description
 */
class BasicBlock extends Record implements BlockInterface, Arrayable
{
    public const BLOCK_TYPE = 'block';

    /**
     * @var BlockInterface
     */
    protected $parent;

    /**
     * @var Collection|Property[]
     */
    protected $properties;

    /**
     * @var string
     */
    protected $format;

    public function __construct(UuidInterface $id, $recordMap)
    {
        parent::__construct($id, $recordMap);
        $this->attributes = Arr::get($this->recordMap, 'block.'.$this->id.'.value');
    }

    public function __get($name)
    {
        switch ($name) {
            case 'id':
                return $this->getId()->toString();

            case 'created_time':
            case 'last_edited_time':
                return new \DateTime('@'.round($this->get($name) / 1000));

            case 'contents':
            case 'cover':
            case 'icon':
            case 'description':
            case 'title':
                return $this->{'get'.ucfirst($name)}();

            default:
                $property = $this->getProperty($name);

                return $property ? $property->getValue() : null;
        }
    }

    public function __set($name, $value): void
    {
        $this->setProperty($name, $value);
    }

    protected function getBlockTypes()
    {
        return [
            'collection_view_page' => CollectionViewBlock::class,
            BasicBlock::BLOCK_TYPE => BasicBlock::class,
            TextBlock::BLOCK_TYPE => TextBlock::class,
            CollectionBlock::BLOCK_TYPE => CollectionBlock::class,
            CollectionViewBlock::BLOCK_TYPE => CollectionViewBlock::class,
            PageBlock::BLOCK_TYPE => PageBlock::class,
            CodeBlock::BLOCK_TYPE => CodeBlock::class,
            SubHeaderBlock::BLOCK_TYPE => SubHeaderBlock::class,
            SubsubHeaderBlock::BLOCK_TYPE => SubsubHeaderBlock::class,
            QuoteBlock::BLOCK_TYPE => QuoteBlock::class,
            ImageBlock::BLOCK_TYPE => ImageBlock::class,
            HeaderBlock::BLOCK_TYPE => HeaderBlock::class,
            ColumnListBlock::BLOCK_TYPE => ColumnListBlock::class,
            ColumnBlock::BLOCK_TYPE => ColumnBlock::class,
            NumberedListBlock::BLOCK_TYPE => NumberedListBlock::class,
        ];
    }

    public function toTypedBlock(): BasicBlock
    {
        $types = $this->getBlockTypes();
        $blockType =
            $this->get('parent_table') === 'collection'
                ? CollectionRowBlock::class
                : $types[$this->get('type')] ?? null;
        $block = $blockType ? new $blockType($this->getId(), $this->getRecordMap()) : $this;

        if ($this->client) {
            $block->setClient($this->client);
        }

        return $block;
    }

    public function getTitle(): string
    {
        return (string) $this->getProperty('title');
    }

    public function getTable(): string
    {
        return 'block';
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getCover()
    {
        return Utils::signUrl($this->get('format.page_cover') ?? '');
    }

    public function getIcon(): string
    {
        return Utils::signUrl($this->get('format.page_icon') ?? '');
    }

    public function getDescription(): string
    {
        return $this->getUnwrapped('description');
    }

    public function getParent(): ?BlockInterface
    {
        $isAlias = false;
        if (!$isAlias) {
            $parentId = $this->get('parent_id');
            $parentTable = $this->get('parent_table');
        } else {
            $parentId = $this->_alias_parent;
            $parentTable = 'block';
        }

        switch ($parentTable) {
            case 'block':
                return $this->getClient()->getBlock($parentId);

            case 'collection':
                return $this->getClient()->getCollection($parentId);

            case 'space':
                return $this->getClient()->getSpace($parentId);

            default:
                return null;
        }
    }

    public function getProperties(): Collection
    {
        return $this->properties;
    }

    public function setProperties(Collection $properties): void
    {
        $this->properties = $properties;
    }

    public function createProperties(array $schemas = []): void
    {
        if ($schemas) {
            $this->setProperties(
                collect($schemas)->mapWithKeys(function ($schema, $hash) {
                    $schema['hash'] = $hash;
                    $property = $this->unwrapValue($this->get('properties.'.$hash) ?? []);
                    $name = $schema['name'] ?? '';

                    return [$name => new Property($schema, $property)];
                })
            );
        } else {
            $this->setProperties(
                collect($this->get('properties'))->map(function ($property) {
                    return new Property([], $this->unwrapValue($property));
                })
            );
        }
    }

    public function getProperty(string $needle): ?Property
    {
        return $this->properties->first(function (Property $property, $key) use ($needle) {
            return $key === $needle ||
                Str::slug($property->getName()) === $needle ||
                Str::snake($property->getName()) === $needle;
        });
    }

    public function setProperty(string $key, $value): void
    {
        $property = $this->getProperty($key);
        if (!$property) {
            return;
        }

        $property->setValue($value);
        $operation = new BuildOperation(
            $this->getId(),
            $property->getPath(),
            [[$property->getRawValue()]],
            'set',
            $this->getTable()
        );

        $this->getClient()->submitTransation([$operation]);
    }

    protected function getUnwrapped(string $propertyName): string
    {
        $property = $this->get($propertyName) ?? [];

        return $this->unwrapValue($property);
    }

    /**
     * @return mixed|string
     */
    protected function unwrapValue(array $property)
    {
        return collect($property)
            ->map(function ($chunk) {
                if (count($chunk) === 1) {
                    return $chunk[0];
                }

                [$text, $format] = $chunk;
                $options = $format[0][1] ?? [];
                $format = $format[0][0];
                if (!trim($text)) {
                    return;
                }

                switch ($format) {
                    case 'i':
                        return '*'.trim($text).'*';
                    case 'b':
                        return '**'.trim($text).'**';
                    case 'd':
                        return $options['start_date'];
                    case 'p':
                        return $text;
                    case 'a':
                        return sprintf('[%s](%s)', $text, $options);
                    case 'c':
                        return '`'.$text.'`';
                    default:
                        return $text;
                }
            })
            ->join(' ');
    }

    public function getCollection(): ?CollectionBlock
    {
        return null;
    }

    public function getChildren(): Collection
    {
        return $this->toChildBlocks(
            collect($this->get('content'))->map(function (string $id) {
                return $this->client->getBlock($id);
            })
        );
    }

    public function getContents()
    {
        return $this->getChildren()
            ->map(function (BasicBlock $block) {
                return $block->toString();
            })
            ->join(' ');
    }

    protected function toChildBlocks(Collection $blocks): Collection
    {
        return collect($blocks)->map(function (BasicBlock $block) {
            $block->setClient($this->getClient());
            $block->createProperties($this->get('schema') ?? []);

            return $block;
        });
    }

    public function toString()
    {
        $title = $this->getTitle();
        if ($this->get('content')) {
            $title .= $this->getContents();
        }

        return $title.PHP_EOL.PHP_EOL;
    }

    public function toHtml()
    {
        $converter = new CommonMarkConverter();

        return $converter->convertToHtml($this->getContents());
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toArray()
    {
        return [
            'id' => $this->getId()->toString(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'properties' => $this->properties->toArray(),
            'attributes' => $this->attributes,
        ];
    }
}
