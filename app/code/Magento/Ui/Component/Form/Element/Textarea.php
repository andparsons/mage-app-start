<?php
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 100.0.2
 */
class Textarea extends AbstractElement
{
    const NAME = 'textarea';

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
