<?php
namespace Magento\Config\Model\Config\Structure\Element\Iterator;

/**
 * @api
 * @since 100.0.2
 */
class Section extends \Magento\Config\Model\Config\Structure\Element\Iterator
{
    /**
     * @param \Magento\Config\Model\Config\Structure\Element\Section $element
     */
    public function __construct(\Magento\Config\Model\Config\Structure\Element\Section $element)
    {
        parent::__construct($element);
    }
}
