<?php
namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * Class Price
 */
class Price extends AbstractDataType
{
    const NAME = 'price';

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
