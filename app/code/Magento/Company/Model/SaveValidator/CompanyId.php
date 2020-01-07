<?php

namespace Magento\Company\Model\SaveValidator;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Checks if company id is correct.
 */
class CompanyId implements \Magento\Company\Model\SaveValidatorInterface
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $company;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $initialCompany;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\Company\Api\Data\CompanyInterface $initialCompany
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\Company\Api\Data\CompanyInterface $initialCompany
    ) {
        $this->company = $company;
        $this->initialCompany = $initialCompany;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->company->getId() && !$this->initialCompany->getId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'companyId',
                        'fieldValue' => $this->company->getId()
                    ]
                )
            );
        }
    }
}
