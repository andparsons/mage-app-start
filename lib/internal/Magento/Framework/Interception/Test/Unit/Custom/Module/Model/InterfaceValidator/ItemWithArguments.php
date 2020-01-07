<?php
namespace Magento\Framework\Interception\Test\Unit\Custom\Module\Model\InterfaceValidator;

class ItemWithArguments
{
    /**
     * @param string $name
     * @return string
     */
    public function getItem($name = 'default')
    {
        return $name;
    }
}
