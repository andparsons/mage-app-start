<?php

namespace Magento\NegotiableQuoteSharedCatalog\Block\Adminhtml\Quote\View;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Add shared catalog information to quote.
 *
 * @api
 * @since 100.0.0
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator
     */
    private $sharedCatalogLocator;

    /**
     * @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    private $sharedCatalog;

    /**
     * @var \Magento\NegotiableQuote\Model\PurgedContentFactory
     */
    private $purgedContentFactory;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\CustomerGroup
     */
    private $groupBlock;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator
     * @param \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\CustomerGroup $groupBlock
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\SharedCatalog\Model\SharedCatalogLocator $sharedCatalogLocator,
        \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\CustomerGroup $groupBlock,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->companyManagement = $companyManagement;
        $this->sharedCatalogLocator = $sharedCatalogLocator;
        $this->purgedContentFactory = $purgedContentFactory;
        $this->jsonDecoder = $jsonDecoder;
        $this->groupBlock = $groupBlock;
    }

    /**
     * Retrieve Shared Catalog edit url.
     *
     * @return string
     */
    public function getSharedCatalogUrl()
    {
        $sharedCatalogUrl = '';

        if ($this->getSharedCatalog() && $this->getSharedCatalog()->getId()) {
            $sharedCatalogUrl = $this->getUrl(
                'shared_catalog/sharedCatalog/edit',
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $this->getSharedCatalog()->getId()]
            );
        } elseif ($this->groupBlock->getGroupUrl()) {
            $sharedCatalogUrl = $this->groupBlock->getGroupUrl();
        }

        return $sharedCatalogUrl;
    }

    /**
     * Retrieve Shared Catalog name.
     *
     * @return string
     */
    public function getSharedCatalogName()
    {
        $sharedCatalogName = '';

        if ($this->getSharedCatalog() && $this->getSharedCatalog()->getName()) {
            $sharedCatalogName = $this->getSharedCatalog()->getName();
        } elseif ($this->groupBlock->getGroupName()) {
            $sharedCatalogName = $this->groupBlock->getGroupName();
        }

        return $sharedCatalogName;
    }

    /**
     * Retrieve Shared Catalog.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     */
    private function getSharedCatalog()
    {
        if ($this->sharedCatalog === null) {
            try {
                $customerGroupId = $this->groupBlock->getCustomerGroupId();
                $this->sharedCatalog = $this->sharedCatalogLocator->getSharedCatalogByCustomerGroup($customerGroupId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->sharedCatalog = null;
            }
        }

        return $this->sharedCatalog;
    }
}
