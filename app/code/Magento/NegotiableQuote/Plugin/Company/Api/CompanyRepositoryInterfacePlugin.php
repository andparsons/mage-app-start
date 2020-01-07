<?php

namespace Magento\NegotiableQuote\Plugin\Company\Api;

/**
 * Class CompanyRepositoryInterfacePlugin handles updates in company.
 */
class CompanyRepositoryInterfacePlugin
{
    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Extractor
     */
    private $extractor;

    /**
     * @var \Magento\Company\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\CompanyQuoteConfigRepositoryInterface
     */
    private $companyQuoteConfigRepository;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company
     */
    private $companyHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\Purged\Handler
     */
    private $purgedContentsHandler;

    /**
     * CompanyRepositoryInterfacePlugin constructor.
     *
     * @param \Magento\NegotiableQuote\Model\Purged\Extractor $extractor
     * @param \Magento\Company\Model\ResourceModel\Customer $customerResource
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\NegotiableQuote\Api\CompanyQuoteConfigRepositoryInterface $companyQuoteConfigRepository
     * @param \Magento\NegotiableQuote\Helper\Company $companyHelper
     * @param \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid $quoteGrid
     * @param \Magento\NegotiableQuote\Model\Purged\Handler $purgedContentsHandler
     */
    public function __construct(
        \Magento\NegotiableQuote\Model\Purged\Extractor $extractor,
        \Magento\Company\Model\ResourceModel\Customer $customerResource,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\NegotiableQuote\Api\CompanyQuoteConfigRepositoryInterface $companyQuoteConfigRepository,
        \Magento\NegotiableQuote\Helper\Company $companyHelper,
        \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid $quoteGrid,
        \Magento\NegotiableQuote\Model\Purged\Handler $purgedContentsHandler
    ) {
        $this->extractor = $extractor;
        $this->customerResource = $customerResource;
        $this->customerRepository = $customerRepository;
        $this->companyQuoteConfigRepository = $companyQuoteConfigRepository;
        $this->companyHelper = $companyHelper;
        $this->quoteGrid = $quoteGrid;
        $this->purgedContentsHandler = $purgedContentsHandler;
    }

    /**
     * Refresh quote grid if there were changes in company.
     *
     * @param \Magento\Company\Api\CompanyRepositoryInterface $subject
     * @param \Closure $proceed
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Company\Api\CompanyRepositoryInterface $subject,
        \Closure $proceed,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        $companyNameChanged = false;

        if ($company->getId() && $company->dataHasChangedFor(\Magento\Company\Api\Data\CompanyInterface::NAME)) {
            $companyNameChanged = true;
        }

        /** @var \Magento\Company\Api\Data\CompanyInterface $result */
        $result = $proceed($company);

        if ($companyNameChanged) {
            $this->quoteGrid->refreshValue(
                \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::COMPANY_ID,
                $company->getId(),
                \Magento\NegotiableQuote\Model\ResourceModel\QuoteGrid::COMPANY_NAME,
                $company->getCompanyName()
            );
        }

        if ($company) {
            $quoteConfig = $this->companyHelper->getQuoteConfig($company);
            $quoteConfig->setCompanyId($company->getId());
            $this->companyQuoteConfigRepository->save($quoteConfig);
        }

        return $result;
    }

    /**
     * Store user specified data in quote before delete company.
     *
     * @param \Magento\Company\Api\CompanyRepositoryInterface $subject
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        \Magento\Company\Api\CompanyRepositoryInterface $subject,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        $companyMembers = $this->customerResource->getCustomerIdsByCompanyId($company->getId());

        foreach ($companyMembers as $companyMemberId) {
            $customer = $this->customerRepository->getById($companyMemberId);
            $associatedCustomerData = $this->extractor->extractCustomer($customer);
            $this->purgedContentsHandler->process($associatedCustomerData, $companyMemberId);
        }
    }
}
