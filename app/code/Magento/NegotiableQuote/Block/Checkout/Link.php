<?php

namespace Magento\NegotiableQuote\Block\Checkout;

use Magento\Framework\File\Size;
use Magento\NegotiableQuote\Model\Config as NegotiableQuoteConfig;

/**
 * Request negotiable quote link.
 *
 * @api
 * @since 100.0.0
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\NegotiableQuote\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $quoteHelper;

    /**
     * @var Size
     */
    private $fileSize;

    /**
     * @var \Magento\NegotiableQuote\Model\Config
     */
    private $negotiableQuoteConfig;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * Link constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\NegotiableQuote\Helper\Config $configHelper
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param Size $fileSize
     * @param NegotiableQuoteConfig $negotiableQuoteConfig
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\NegotiableQuote\Helper\Config $configHelper,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        Size $fileSize,
        NegotiableQuoteConfig $negotiableQuoteConfig,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->userContext = $userContext;
        $this->configHelper = $configHelper;
        $this->cartManagement = $cartManagement;
        $this->quoteHelper = $quoteHelper;
        $this->fileSize = $fileSize;
        $this->negotiableQuoteConfig = $negotiableQuoteConfig;
        $this->authorization = $authorization;
    }

    /**
     * Is quote request allowed.
     *
     * @return bool
     */
    public function isQuoteRequestAllowed()
    {
        $quote = $this->getCurrentQuote();
        return $this->configHelper->isQuoteAllowed($quote);
    }

    /**
     * Is quote request enabled.
     *
     * @return bool
     */
    public function isQuoteRequestEnabled()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }

    /**
     * Get disallow message.
     *
     * @return string
     */
    public function getDisallowMessage()
    {
        return $this->configHelper->getMinimumAmountMessage();
    }

    /**
     * Get current quote.
     *
     * @return \Magento\Quote\Model\Quote
     */
    private function getCurrentQuote()
    {
        return $this->cartManagement->getCartForCustomer($this->userContext->getUserId());
    }

    /**
     * Retrieve request negotiable quote URL.
     *
     * @return string
     */
    public function getCreateNegotiableQuoteUrl()
    {
        return $this->getUrl('negotiable_quote/quote/create');
    }

    /**
     * Retrieve check quote discount url.
     *
     * @return string
     */
    public function getCheckQuoteDiscountsUrl()
    {
        return $this->getUrl('negotiable_quote/quote/checkDiscount');
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->quoteHelper->isEnabled() ? parent::_toHtml() : '';
    }

    /**
     * Get max file size.
     *
     * @return float
     */
    public function getMaxFileSize()
    {
        return $this->fileSize->convertSizeToInteger($this->getMaxFileSizeMb() . 'M');
    }

    /**
     * Get allowed file extensions.
     *
     * @return string
     */
    public function getAllowedExtensions()
    {
        return $this->negotiableQuoteConfig->getAllowedExtensions();
    }

    /**
     * Get max file size in Mb.
     *
     * @return float
     */
    public function getMaxFileSizeMb()
    {
        $configSize = $this->negotiableQuoteConfig->getMaxFileSize();
        $phpLimit = $this->fileSize->getMaxFileSizeInMb();
        if ($configSize) {
            return min($configSize, $phpLimit);
        }
        return $phpLimit;
    }
}
