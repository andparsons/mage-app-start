<?php
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ObserverInterface
 */
interface ObserverInterface
{
    /**
     * Update component according to $component
     *
     * @param UiComponentInterface $component
     * @return void
     */
    public function update(UiComponentInterface $component);
}
