<?php
namespace Magento\Framework\View\Element\Message\Renderer\BlockRenderer;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array_merge(
            (array)$this->getData(),
            [
                'MESSAGE',
                $this->getTemplate(),
                $this->_storeManager->getStore()->getCode()
            ]
        );
    }
}
