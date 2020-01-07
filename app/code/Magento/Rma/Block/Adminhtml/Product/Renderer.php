<?php

/**
 * Additional Renderer of Product's Attribute Enable RMA control structure
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Rma\Block\Adminhtml\Product;

class Renderer extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        if ($this->getValue() === null) {
            $this->setValue(\Magento\Rma\Model\Product\Source::ATTRIBUTE_ENABLE_RMA_USE_CONFIG);
        }
        return parent::getElementHtml();
    }
}
