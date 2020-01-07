<?php
namespace Magento\Framework\View\Element\UiComponent\ContentType;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface ContentTypeInterface
 */
interface ContentTypeInterface
{
    /**
     * Render component
     *
     * @param UiComponentInterface $component
     * @param string $template
     * @return string
     */
    public function render(UiComponentInterface $component, $template = '');
}
