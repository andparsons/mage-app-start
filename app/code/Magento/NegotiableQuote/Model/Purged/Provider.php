<?php

namespace Magento\NegotiableQuote\Model\Purged;

/**
 * Class provides stored data of deleted related entities.
 */
class Provider
{
    /**
     * @var \Magento\NegotiableQuote\Model\PurgedContentFactory
     */
    private $purgedContentFactory;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @var array
     */
    private $salesRepresentativeNames = [];

    /**
     * @param \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
    ) {
        $this->purgedContentFactory = $purgedContentFactory;
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
    }

    /**
     * Get customer name.
     *
     * @param int $quoteId
     * @return string
     */
    public function getCustomerName($quoteId)
    {
        return $this->getStoredField($quoteId, 'customer_name');
    }

    /**
     * Get company ID.
     *
     * @param int $quoteId
     * @return int
     */
    public function getCompanyId($quoteId)
    {
        return $this->getStoredField($quoteId, \Magento\Company\Api\Data\CompanyInterface::COMPANY_ID);
    }

    /**
     * Get company name.
     *
     * @param int $quoteId
     * @return string
     */
    public function getCompanyName($quoteId)
    {
        return $this->getStoredField($quoteId, \Magento\Company\Api\Data\CompanyInterface::NAME);
    }

    /**
     * Get company email.
     *
     * @param int $quoteId
     * @return string
     */
    public function getCompanyEmail($quoteId)
    {
        return $this->getStoredField($quoteId, \Magento\Company\Api\Data\CompanyInterface::EMAIL);
    }

    /**
     * Get sales representative id.
     *
     * @param int $quoteId
     * @return string
     */
    public function getSalesRepresentativeId($quoteId)
    {
        return $this->getStoredField($quoteId, \Magento\Company\Api\Data\CompanyInterface::SALES_REPRESENTATIVE_ID);
    }

    /**
     * Get sales representative name.
     *
     * @param int $quoteId
     * @return string
     */
    public function getSalesRepresentativeName($quoteId)
    {
        if (empty($this->salesRepresentativeNames[$quoteId])) {
            $this->salesRepresentativeNames[$quoteId] = $this->negotiableQuoteHelper->getSalesRepresentative($quoteId)
                ?: $this->getStoredField($quoteId, 'sales_representative_name');
        }
        return $this->salesRepresentativeNames[$quoteId];
    }

    /**
     * Get field from purged data storage.
     *
     * @param int $quoteId
     * @param string $field
     * @return string
     */
    private function getStoredField($quoteId, $field)
    {
        $purgedContents = $this->purgedContentFactory->create()->load($quoteId);
        $purgedData = json_decode($purgedContents->getPurgedData(), true);

        return isset($purgedData[$field]) ? $purgedData[$field] : '';
    }
}
