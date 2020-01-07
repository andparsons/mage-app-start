<?php

namespace Magento\Company\Model\SaveHandler;

use Magento\Company\Model\SaveHandlerInterface;
use Magento\Company\Api\Data\CompanyInterface;

/**
 * Company status save handler.
 */
class CompanyStatus implements SaveHandlerInterface
{
    const ALL = '*';

    /**
     * Email templates config.
     *
     * @var array
     */
    private $emailTemplatesConfig = [
        CompanyInterface::STATUS_PENDING => [
            CompanyInterface::STATUS_APPROVED
                => 'company/email/company_status_pending_approval_to_active_template'
        ],
        CompanyInterface::STATUS_REJECTED => [
            CompanyInterface::STATUS_APPROVED
                => 'company/email/company_status_rejected_blocked_to_active_template'
        ],
        CompanyInterface::STATUS_BLOCKED => [
            CompanyInterface::STATUS_APPROVED
                => 'company/email/company_status_rejected_blocked_to_active_template'
        ],
        self::ALL => [
            CompanyInterface::STATUS_PENDING
                => 'company/email/company_status_pending_approval_template',
            CompanyInterface::STATUS_REJECTED
                => 'company/email/company_status_rejected_template',
            CompanyInterface::STATUS_BLOCKED
                => 'company/email/company_status_blocked_template'
        ],
    ];

    /**
     * @var \Magento\Company\Model\Email\Sender
     */
    private $companyEmailSender;

    /**
     * @var \Magento\Company\Model\CompanyManagement
     */
    private $companyManagement;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company
     */
    private $companyResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @param \Magento\Company\Model\Email\Sender $companyEmailSender
     * @param \Magento\Company\Model\CompanyManagement $companyManagement
     * @param \Magento\Company\Model\ResourceModel\Company $companyResource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Company\Model\Email\Sender $companyEmailSender,
        \Magento\Company\Model\CompanyManagement $companyManagement,
        \Magento\Company\Model\ResourceModel\Company $companyResource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->companyEmailSender = $companyEmailSender;
        $this->companyManagement = $companyManagement;
        $this->companyResource = $companyResource;
        $this->date = $date;
    }

    /**
     * @inheritdoc
     */
    public function execute(CompanyInterface $company, CompanyInterface $initialCompany)
    {
        if ($initialCompany->getStatus() != $company->getStatus()) {
            $companyAdmin = $this->companyManagement->getAdminByCompanyId($company->getId());
            $template = $this->getEmailTemplate($initialCompany->getStatus(), $company->getStatus());

            if ($template && $companyAdmin) {
                $this->companyEmailSender->sendCompanyStatusChangeNotificationEmail(
                    $companyAdmin,
                    $company->getId(),
                    $template
                );
            }
            $this->updateCompanyRejectedAtDate($company, $initialCompany);
        }
    }

    /**
     * Get email template path.
     *
     * @param int $oldStatus
     * @param int $newStatus
     * @return string
     */
    private function getEmailTemplate($oldStatus, $newStatus)
    {
        if (isset($this->emailTemplatesConfig[$oldStatus][$newStatus])) {
            return $this->emailTemplatesConfig[$oldStatus][$newStatus];
        } else {
            if (isset($this->emailTemplatesConfig[self::ALL][$newStatus])) {
                return $this->emailTemplatesConfig[self::ALL][$newStatus];
            }
        }

        return null;
    }

    /**
     * Update company rejected at date.
     *
     * @param CompanyInterface $company
     * @param CompanyInterface $initialCompany
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function updateCompanyRejectedAtDate(
        CompanyInterface $company,
        CompanyInterface $initialCompany
    ) {
        if ($company->getStatus() == CompanyInterface::STATUS_REJECTED
            && $initialCompany->getStatus() != CompanyInterface::STATUS_REJECTED
        ) {
            $company->setRejectedAt($this->date->gmtDate());
            $this->companyResource->save($company);
        }
    }
}
