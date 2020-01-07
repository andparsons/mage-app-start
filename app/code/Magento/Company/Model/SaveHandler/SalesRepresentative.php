<?php

namespace Magento\Company\Model\SaveHandler;

use Magento\Company\Model\SaveHandlerInterface;
use Magento\Company\Api\Data\CompanyInterface;

/**
 * Company sales representative save handler.
 */
class SalesRepresentative implements SaveHandlerInterface
{
    /**
     * @var \Magento\Company\Model\Email\Sender
     */
    private $companyEmailSender;

    /**
     * @param \Magento\Company\Model\Email\Sender $companyEmailSender
     */
    public function __construct(
        \Magento\Company\Model\Email\Sender $companyEmailSender
    ) {
        $this->companyEmailSender = $companyEmailSender;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(CompanyInterface $company, CompanyInterface $initialCompany)
    {
        if ($initialCompany->getSalesRepresentativeId() != $company->getSalesRepresentativeId()) {
            $this->companyEmailSender->sendSalesRepresentativeNotificationEmail(
                $company->getId(),
                $company->getSalesRepresentativeId()
            );
        }
    }
}
