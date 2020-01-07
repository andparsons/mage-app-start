<?php
namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * Class Boolean
 */
class Boolean extends AbstractDataType
{
    const NAME = 'boolean';

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
