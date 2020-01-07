<?php

/**
 * Order by SKU Widget Block
 *
 */
namespace Magento\AdvancedCheckout\Block\Widget;

class Sku extends \Magento\AdvancedCheckout\Block\Sku\AbstractSku implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Retrieve form action URL
     *
     * @codeCoverageIgnore
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('checkout/cart/advancedAdd');
    }
}
