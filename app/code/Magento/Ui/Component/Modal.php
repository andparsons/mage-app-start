<?php
namespace Magento\Ui\Component;

/**
 * @api
 * @since 100.1.0
 */
class Modal extends AbstractComponent
{
    const NAME = 'modal';

    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
