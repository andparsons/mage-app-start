<?php

namespace Magento\CompanyCredit\Plugin\Company\Model;

/**
 * Create company credit for company if the company does not have company credit.
 */
class CompanyCreditCreatePlugin
{
    /**
     * @var \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory
     */
    private $creditLimitFactory;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\CompanyCredit\Api\Data\CreditLimitInterfaceFactory $creditLimitFactory,
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->creditLimitFactory = $creditLimitFactory;
        $this->creditLimitRepository = $creditLimitRepository;
        $this->creditLimitManagement = $creditLimitManagement;
        $this->websiteRepository = $websiteRepository;
        $this->request = $request;
    }

    /**
     * Create company credit for company if the company does not have company credit.
     *
     * @param \Magento\Company\Model\Company\Save $subject
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @return \Magento\Company\Api\Data\CompanyInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Company\Model\Company\Save $subject,
        \Magento\Company\Api\Data\CompanyInterface $company
    ) {
        /** @var \Magento\CompanyCredit\Api\Data\CreditLimitInterface $creditLimit */
        try {
            $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($company->getId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $creditLimit = $this->creditLimitFactory->create();
            $creditLimit->setCompanyId($company->getId());
        }
        if (!$creditLimit->getId() && !$this->request->getParam('company_credit')) {
            $creditLimit->setCompanyId($company->getId());
            $creditLimit->setCurrencyCode($this->websiteRepository->getDefault()->getBaseCurrencyCode());
            $this->creditLimitRepository->save($creditLimit);
        }
        return $company;
    }
}
