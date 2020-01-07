<?php

namespace Magento\Company\Model\SaveValidator;

use \Magento\Company\Api\Data\CompanyInterface;

/**
 * Checks if company has all required fields.
 */
class CompanyStatus implements \Magento\Company\Model\SaveValidatorInterface
{
    /**
     * @var array
     */
    private $allowedStatuses = [
        CompanyInterface::STATUS_APPROVED,
        CompanyInterface::STATUS_BLOCKED,
        CompanyInterface::STATUS_PENDING,
        CompanyInterface::STATUS_REJECTED
    ];

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * @var \Magento\Framework\Exception\InputException
     */
    private $exception;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\Framework\Exception\InputException $exception
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\Framework\Exception\InputException $exception
    ) {
        $this->company = $company;
        $this->exception = $exception;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!in_array($this->company->getStatus(), $this->allowedStatuses)) {
            $this->exception->addError(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['fieldName' => 'status', 'value' => $this->company->getStatus()]
                )
            );
        }
    }
}
