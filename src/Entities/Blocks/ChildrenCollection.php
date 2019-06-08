<?php

namespace Notion\Entities\Blocks;

use Illuminate\Support\Collection;
use Notion\ClientAware;

class ChildrenCollection extends Collection
{
    use ClientAware;

    /**
     * @var BlockInterface
     */
    protected $parent;

    public function __construct($items = [], BlockInterface $parent = null)
    {
        parent::__construct($items);

        $this->parent = $parent;
    }

    public function createBlock(string $type, array $children = []): BlockInterface
    {
        $blockType = $type::BLOCK_TYPE;

        $blockId = $this->getClient()->createRecord('block', $this->parent, $blockType, $children);
        $block = $this->getClient()->getBlock($blockId->toString());
        dd($block);

        return $block;
        // determine the block type string from the Block class, if that's what was provided
        //        if isinstance(block_type, type) and issubclass(block_type, Block) and hasattr(block_type, "_type"):
        //            block_type = block_type._type
        //        elif not isinstance(block_type, str):
        //            raise Exception("block_type must be a string or a Block subclass with a _type attribute")
        //
        //        block_id = self._client.create_record(table="block", parent=self._parent, type=block_type, child_list_key=child_list_key)
        //
        //        block = self._get_block(block_id)
        //
        //        if kwargs:
        //            with self._client.as_atomic_transaction():
        //                for key, val in kwargs.items():
        //                    if hasattr(block, key):
        //                        setattr(block, key, val)
        //                    else:
        //                        logging.warning("{} does not have attribute '{}' to be set; skipping.".format(block, key))
        //
        //        return block
    }
}
