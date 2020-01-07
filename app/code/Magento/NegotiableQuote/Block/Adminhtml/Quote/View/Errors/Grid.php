<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Errors;

/**
 * Quote errors grid widget block
 *
 * @api
 * @since 100.0.0
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        array $data = []
    ) {
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Get prepared collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $collection = $this->getCollection();
        $customerGroupId = $this->getQuoteCustomerGroupId();
        $collection->setCustomerGroupId($customerGroupId);
        return $collection;
    }

    /**
     * Retrieve quote customerGroupId
     *
     * @return int
     */
    private function getQuoteCustomerGroupId()
    {
        $quoteId = $this->_request->getParam('quote_id');
        $customerGroupId = 0;

        if ($quoteId) {
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->quoteRepository->get($quoteId, ['*']);
            $customerGroupId = $quote->getCustomerGroupId();
        }

        return $customerGroupId;
    }
}
