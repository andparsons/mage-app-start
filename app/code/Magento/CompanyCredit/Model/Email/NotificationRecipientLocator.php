<?php

namespace Magento\CompanyCredit\Model\Email;

/**
 * Class that retrieves email notification recipient to use in Sender class.
 */
class NotificationRecipientLocator
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface
     */
    private $creditLimitRepository;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * NotificationRecipient constructor.
     *
     * @param \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\CompanyCredit\Api\CreditLimitRepositoryInterface $creditLimitRepository,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement
    ) {
        $this->creditLimitRepository = $creditLimitRepository;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Get company admin by credit history record.
     *
     * @param \Magento\CompanyCredit\Model\HistoryInterface $creditHistoryRecord
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByRecord(\Magento\CompanyCredit\Model\HistoryInterface $creditHistoryRecord)
    {
        $creditLimit = $this->creditLimitRepository->get($creditHistoryRecord->getCompanyCreditId());
        $companySuperUser = $this->companyManagement->getAdminByCompanyId($creditLimit->getCompanyId());

        return $companySuperUser;
    }
}
