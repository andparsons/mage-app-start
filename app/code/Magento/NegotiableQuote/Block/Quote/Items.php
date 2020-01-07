<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\NegotiableQuote\Helper\Quote as NegotiableQuoteHelper;

/**
 * Class for quote items rendering for the negotiable quotes.
 *
 * @api
 * @since 100.0.0
 */
class Items extends AbstractQuote
{
    /**#@+
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';
    /**#@-*/

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @param TemplateContext $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param NegotiableQuoteHelper $negotiableQuoteHelper
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param array $data [optional]
     */
    public function __construct(
        TemplateContext $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        NegotiableQuoteHelper $negotiableQuoteHelper,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $negotiableQuoteHelper, $data);
        $this->taxHelper = $taxHelper;
        $this->authorization = $authorization;
    }

    /**
     * Get quote helper.
     *
     * @return NegotiableQuoteHelper
     */
    public function getQuoteHelper()
    {
        return $this->negotiableQuoteHelper;
    }

    /**
     * Get tax helper.
     *
     * @return \Magento\Tax\Helper\Data
     */
    public function getTaxHelper()
    {
        return $this->taxHelper;
    }

    /**
     * Return customer quote items.
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];

        if ($this->getQuote(true)) {
            $items = $this->getQuote(true)->getAllVisibleItems();
        }

        return $items;
    }

    /**
     * Retrieve renderer list.
     *
     * @return \Magento\Framework\View\Element\RendererList
     */
    protected function _getRendererList()
    {
        return $this->getRendererListName()
            ? $this->getLayout()->getBlock($this->getRendererListName())
            : $this->getChildBlock('renderer.list');
    }

    /**
     * Retrieve item renderer block.
     *
     * @param string|null $type [optional]
     * @return \Magento\Framework\View\Element\Template
     * @throws LocalizedException
     */
    public function getItemRenderer($type = null)
    {
        if ($type === null) {
            $type = self::DEFAULT_TYPE;
        }
        $rendererList = $this->_getRendererList();
        if (!$rendererList) {
            throw new LocalizedException(__('Renderer list for block "%1" is not defined', $this->getNameInLayout()));
        }
        $overriddenTemplates = $this->getOverriddenTemplates() ?: [];
        $template = isset($overriddenTemplates[$type]) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
        return $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
    }

    /**
     * Get item row html.
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface $item
     * @return string
     */
    public function getItemHtml(\Magento\Quote\Api\Data\CartItemInterface $item)
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }

    /**
     * Get customer price changes state.
     *
     * @return bool
     */
    public function isCustomerPriceChanged()
    {
        return $this->getNegotiableQuote() !== null
            && $this->getNegotiableQuote()->getNegotiatedPriceValue() !== null
            && $this->getNegotiableQuote()->getStatus() != NegotiableQuoteInterface::STATUS_EXPIRED
            ? (bool)$this->getNegotiableQuote()->getIsCustomerPriceChanged()
            : false;
    }

    /**
     * Get shipping tax changes state.
     *
     * @return bool
     */
    public function isShippingTaxChanged()
    {
        return $this->getNegotiableQuote() !== null
            && $this->getNegotiableQuote()->getShippingPrice() !== null
            && $this->getNegotiableQuote()->getStatus() != NegotiableQuoteInterface::STATUS_EXPIRED
            ? (bool)$this->getNegotiableQuote()->getIsShippingTaxChanged()
            : false;
    }

    /**
     * Check if negotiable quote management is allowed for this customer.
     *
     * @return bool
     */
    public function isAllowedManage()
    {
        return $this->authorization->isAllowed('Magento_NegotiableQuote::manage');
    }
}
