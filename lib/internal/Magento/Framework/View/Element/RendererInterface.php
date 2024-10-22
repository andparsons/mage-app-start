<?php
namespace Magento\Framework\View\Element;

/**
 * Magento Block interface
 *
 * @api
 * @since 100.0.2
 */
interface RendererInterface
{
    /**
     * Produce html output using the given data source
     *
     * @param mixed $data
     * @return mixed
     */
    public function render($data);
}
