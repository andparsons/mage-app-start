<?php
namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * Class Password
 */
class Password extends AbstractDataType
{
    const NAME = 'password';

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
