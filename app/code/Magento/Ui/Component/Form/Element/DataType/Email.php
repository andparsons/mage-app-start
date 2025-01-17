<?php
namespace Magento\Ui\Component\Form\Element\DataType;

/**
 * Class Email
 */
class Email extends AbstractDataType
{
    const NAME = 'email';

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
