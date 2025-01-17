<?php
namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Backend grid item renderer interface
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
interface RendererInterface
{
    /**
     * Set column for renderer
     *
     * @param Column $column
     * @return void
     * @abstract
     * @api
     */
    public function setColumn($column);

    /**
     * Returns row associated with the renderer
     *
     * @abstract
     * @return void
     * @api
     */
    public function getColumn();

    /**
     * Renders grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     * @api
     */
    public function render(\Magento\Framework\DataObject $row);
}
