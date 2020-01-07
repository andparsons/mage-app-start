<?php
namespace Magento\Framework\View\Element\Message\Renderer;

/**
 * Interface \Magento\Framework\View\Element\Message\Renderer\PoolInterface
 *
 */
interface PoolInterface
{
    /**
     * Returns Renderer for specified identifier
     *
     * @param string $rendererCode
     * @return RendererInterface | null
     */
    public function get($rendererCode);
}
