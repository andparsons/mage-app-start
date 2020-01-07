<?php
namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Items;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Adminhtml sales order create items grid block
 */
class SalesGrid extends \Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid
{
    /**
     * @var CartInterface
     */
    protected $quote;

    /**
     * Retrieve quote model object
     *
     * @return CartInterface
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @param CartInterface $quote
     * @return $this
     */
    public function setQuote(CartInterface $quote)
    {
        $this->quote = $quote;
        return $this;
    }
}
