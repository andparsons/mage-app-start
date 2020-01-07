<?php
namespace Magento\Framework\Mview\View\State;

/**
 * Interface \Magento\Framework\Mview\View\State\CollectionInterface
 *
 */
interface CollectionInterface
{
    /**
     * Retrieve loaded states
     *
     * @return array
     */
    public function getItems();
}
