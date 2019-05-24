<?php

namespace Notion\Blocks;

use Notion\Identifier;

class CollectionBlock extends BasicBlock
{
    public function getTitle(): string
    {
        return $this->getTextAttribute('name') ?: parent::getTitle();
    }

    /**
     * @return BlockInterface[]
     */
    public function getChildren(): array
    {
        $pages = $this->getClient()->getByParent($this->getId());
        $blocks = collect($pages['block'])
            ->keys()
            ->map(function ($id) use ($pages) {
                $block = (new BasicBlock(
                    Identifier::fromString($id),
                    $pages
                ))->toTypedBlock();
                $block->setClient($this->getClient());

                $schema = $this->get('schema');
                $properties = collect($block->get('properties'))->mapWithKeys(
                    function ($property, $hash) use ($schema) {
                        $name = $schema[$hash]['name'] ?? '';

                        return [$name => $property];
                    }
                );
                $block->set('properties', $properties->toArray());

                return $block;
            });

        return $blocks->toArray();
    }
}
