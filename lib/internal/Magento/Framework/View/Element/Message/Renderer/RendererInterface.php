<?php
namespace Magento\Framework\View\Element\Message\Renderer;

use Magento\Framework\Message\MessageInterface;

/**
 * Interface \Magento\Framework\View\Element\Message\Renderer\RendererInterface
 *
 */
interface RendererInterface
{
    /**
     * Renders message
     *
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function render(MessageInterface $message, array $initializationData);
}
