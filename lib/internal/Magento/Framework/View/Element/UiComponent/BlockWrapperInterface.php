<?php
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Interface BlockWrapperInterface
 */
interface BlockWrapperInterface extends UiComponentInterface
{
    /**
     * Get wrapped block
     *
     * @return BlockInterface
     */
    public function getBlock();
}
