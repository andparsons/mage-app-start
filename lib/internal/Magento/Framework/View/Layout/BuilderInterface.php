<?php
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\LayoutInterface;

/**
 * Interface BuilderInterface
 *
 * @api
 * @since 100.0.2
 */
interface BuilderInterface
{
    /**
     * Build structure
     *
     * @return LayoutInterface
     */
    public function build();
}
