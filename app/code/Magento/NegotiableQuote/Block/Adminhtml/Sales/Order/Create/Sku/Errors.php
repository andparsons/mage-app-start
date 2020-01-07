<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Sales\Order\Create\Sku;

/**
 * Form for adding products by SKU.
 *
 * @api
 * @since 100.0.0
 */
class Errors extends \Magento\Backend\Block\Widget
{
    /*
     * JS listType of the error grid.
     */
    const LIST_TYPE = 'errors';

    /**
     * Cart instance.
     *
     * @var \Magento\AdvancedCheckout\Model\Cart|null
     */
    private $cart;

    /**
     * Advanced checkout cart factory.
     *
     * @var \Magento\AdvancedCheckout\Model\CartFactory
     */
    private $cartFactory = null;

    /**
     * List of failed items.
     *
     * @var null|array
     */
    private $failedItems;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\AdvancedCheckout\Model\CartFactory $cartFactory
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\AdvancedCheckout\Model\CartFactory $cartFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cartFactory = $cartFactory;
    }

    /**
     * Returns url to configure item.
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'quotes/quote/configureProductToAdd/',
            ['quote_id' => $this->getRequest()->getParam('quote_id')]
        );
    }

    /**
     * Returns enterprise cart model with custom session for order create page.
     *
     * @return \Magento\AdvancedCheckout\Model\Cart
     */
    public function getCart()
    {
        if (!$this->cart) {
            $this->cart = $this->cartFactory->create()->setSession($this->_backendSession);
        }
        return $this->cart;
    }

    /**
     * Returns current store model.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        $storeId = $this->getCart()->getSession()->getStoreId();
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve items marked as unsuccessful after prepareAddProductsBySku().
     *
     * @return array
     */
    public function getFailedItems()
    {
        if ($this->failedItems === null) {
            $this->failedItems = $this->getCart()->getFailedItems();
        }
        return $this->failedItems;
    }

    /**
     * Get number of failed items.
     *
     * @return int
     */
    public function getNumberOfFailed()
    {
        return count($this->getFailedItems());
    }

    /**
     * Define list type ID.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setListType(self::LIST_TYPE);
    }

    /**
     * Disable output of error grid in case no errors occurred.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->getFailedItems();
        if (empty($this->failedItems)) {
            return '';
        }
        return parent::_toHtml();
    }
}
