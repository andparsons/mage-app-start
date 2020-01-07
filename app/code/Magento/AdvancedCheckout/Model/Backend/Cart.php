<?php
namespace Magento\AdvancedCheckout\Model\Backend;

/**
 * Backend cart model
 *
 */
class Cart extends \Magento\AdvancedCheckout\Model\Cart
{
    /**
     * Return quote instance for backend area
     *
     * @codeCoverageIgnore
     * @return \Magento\Backend\Model\Session\Quote|\Magento\Quote\Model\Quote
     */
    public function getActualQuote()
    {
        return $this->_quote->getQuote();
    }
}
