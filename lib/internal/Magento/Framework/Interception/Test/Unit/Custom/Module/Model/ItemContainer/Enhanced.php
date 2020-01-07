<?php
namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer;

class Enhanced extends \Magento\Framework\Interception\Test\Unit\Custom\Module\Model\ItemContainer
{
    /**
     * @return string
     */
    public function getName()
    {
        return parent::getName() . '_enhanced';
    }
}
