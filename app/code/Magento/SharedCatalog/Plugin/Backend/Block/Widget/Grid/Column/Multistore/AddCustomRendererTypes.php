<?php
declare(strict_types=1);

namespace Magento\SharedCatalog\Plugin\Backend\Block\Widget\Grid\Column\Multistore;

use Magento\Backend\Block\Widget\Grid\Column\Multistore;
use Magento\SharedCatalog\Block\Widget\Grid\Column\Renderer\CustomerGroup;

/**
 * Plugin for modifying renderer types
 */
class AddCustomRendererTypes
{
    /**
     * Change renderer for type "option"
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column\Multistore $subject
     */
    public function beforeGetRenderer(Multistore $subject)
    {
        $subject->setRendererType('options', CustomerGroup::class);
    }
}
