<?php

namespace Magento\NegotiableQuote\Model\Email;

/**
 * Class create quote notification recipient.
 */
class RecipientFactory
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerNameGenerationInterface
     */
    private $customerViewHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerNameGenerationInterface $customerViewHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver
    ) {
        $this->companyManagement = $companyManagement;
        $this->storeManager = $storeManager;
        $this->customerViewHelper = $customerViewHelper;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Prepare quote data for email recipient.
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Framework\DataObject
     */
    public function createForQuote(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $emailData = new \Magento\Framework\DataObject();

        $customer = $quote->getCustomer();
        $storeId = $customer->getStoreId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }
        $emailData->setData('store_id', $storeId);
        $customerName = $this->customerViewHelper->getCustomerName($customer);
        $emailData->setData('customer_name', $customerName);
        $emailData->setData('customer_email', $customer->getEmail());
        $company = $this->companyManagement->getByCustomerId($customer->getId());
        if ($company) {
            $emailData->setData('customer_company', $company->getCompanyName());
        }
        /** @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface $negotiableQuote */
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        if ($negotiableQuote) {
            $emailData->setData('quote_name', $negotiableQuote->getQuoteName());
            $emailData->setData(
                'expiration_period',
                $this->formatExpirationDate(new \DateTime($negotiableQuote->getExpirationPeriod()))
            );
        }

        return $emailData;
    }

    /**
     * Get either first store ID from a set website or the provided as default.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $defaultStoreId [optional]
     * @return int
     */
    private function getWebsiteStoreId(\Magento\Customer\Api\Data\CustomerInterface $customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = array_shift($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Format date for email template.
     *
     * @param \DateTime $date
     * @param int $dateType [optional]
     * @return string
     */
    private function formatExpirationDate(\DateTime $date, $dateType = \IntlDateFormatter::MEDIUM)
    {
        $formatter = new \IntlDateFormatter(
            $this->localeResolver->getLocale(),
            $dateType,
            \IntlDateFormatter::NONE,
            null,
            null,
            null
        );
        return $formatter->format($date);
    }
}
