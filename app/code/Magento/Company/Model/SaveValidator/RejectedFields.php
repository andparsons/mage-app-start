<?php

namespace Magento\Company\Model\SaveValidator;

use Magento\Company\Api\Data\CompanyInterface;

/**
 * Checks if company rejected fields are correct.
 */
class RejectedFields implements \Magento\Company\Model\SaveValidatorInterface
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * @var \Magento\Framework\Exception\InputException
     */
    private $exception;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $initialCompany;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\Company\Api\Data\CompanyInterface $initialCompany
     * @param \Magento\Framework\Exception\InputException $exception
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\Company\Api\Data\CompanyInterface $initialCompany,
        \Magento\Framework\Exception\InputException $exception
    ) {
        $this->company = $company;
        $this->initialCompany = $initialCompany;
        $this->exception = $exception;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (($this->company->getRejectedAt() != $this->initialCompany->getRejectedAt()
                || $this->company->getRejectReason() != $this->initialCompany->getRejectReason())
            && !($this->company->getStatus() == CompanyInterface::STATUS_REJECTED
                && $this->initialCompany->getStatus() != CompanyInterface::STATUS_REJECTED)
        ) {
            $this->exception->addError(
                __(
                    'Invalid attribute value. Rejected date&time and Rejected Reason can be changed only'
                    . ' when a company status is changed to Rejected.'
                )
            );
        }
    }
}
