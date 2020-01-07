<?php
namespace Magento\Ui\Component\Form\Element;

/**
 * @api
 * @since 100.1.0
 */
class ActionDelete extends AbstractElement
{
    const NAME = 'actionDelete';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
