<?php
namespace Magento\Ui\Component\Control;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponent\Control\ControlInterface;

/**
 * Class Link
 */
class Link extends AbstractComponent implements ControlInterface
{
    const NAME = 'link';

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
