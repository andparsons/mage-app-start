<?php
namespace Magento\Backend\Model\Menu;

/**
 * Menu iterator
 * @api
 * @since 100.0.2
 */
class Iterator extends \ArrayIterator
{
    /**
     * Rewind to first element
     *
     * @return void
     */
    public function rewind()
    {
        $this->ksort();
        parent::rewind();
    }
}
