<?php

namespace Magento\CompanyCredit\Plugin\Company\Model\Customer;

/**
 * Create company credit for new companies.
 */
class CompanyPlugin
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory
     */
    private $creditLimitFactory;

    /**
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory
     */
    public function __construct(
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory
    ) {
        $this->creditLimitRepository = $creditLimitRepository;
        $this->creditLimitManagement = $creditLimitManagement;
        $this->creditLimitFactory = $creditLimitFactory;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Save company credit after company creation.
     *
     * @param \Magento\Company\Model\Customer\Company $subject
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCompany(
        \Magento\Company\Model\Customer\Company $subject,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        /** @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit */
        try {
            $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($company->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $creditLimit = $this->creditLimitFactory->create();
            $creditLimit->setCompanyId($company->getId());
        }
        $creditLimit->setCurrencyCode($this->websiteRepository->getDefault()->getBaseCurrencyCode());
        $this->creditLimitRepository->save($creditLimit);
        return $company;
    }
}
