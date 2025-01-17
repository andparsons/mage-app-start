<?php

namespace Magento\Search\Model;

use Magento\Search\Model\Autocomplete\ItemInterface;

/**
 * @api
 * @since 100.0.2
 */
interface AutocompleteInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems();
}
