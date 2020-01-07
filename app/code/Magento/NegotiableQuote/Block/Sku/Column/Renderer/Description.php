<?php
/**
 * SKU failed description block renderer.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NegotiableQuote\Block\Sku\Column\Renderer;

/**
 * Class Description.
 */
class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render block.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $code = $row->getData('code');
        if ($code === \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_DISABLED) {
            $code = \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU;
            $row->setData('code', $code);
        }
        $descriptionBlock = $this->getLayout()->createBlock(
            \Magento\AdvancedCheckout\Block\Adminhtml\Sku\Errors\Grid\Description::class,
            '',
            ['data' => ['product' => $row->getProduct(), 'item' => $row]]
        );

        return $descriptionBlock->toHtml();
    }
}
