<?php
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface JsConfigInterface
 */
interface JsConfigInterface extends UiComponentInterface
{
    /**
     * Get configuration of related JavaScript Component
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getJsConfig(UiComponentInterface $component);
}
