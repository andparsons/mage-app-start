<?php

namespace Magento\Company\Model\SaveValidator;

use \Magento\Company\Api\Data\CompanyInterface;

/**
 * Checks if company has all required fields.
 */
class RequiredFields implements \Magento\Company\Model\SaveValidatorInterface
{
    /**
     * @var array
     */
    private $requiredFields = [
        CompanyInterface::NAME,
        CompanyInterface::COMPANY_EMAIL,
        CompanyInterface::STREET,
        CompanyInterface::CITY,
        CompanyInterface::POSTCODE,
        CompanyInterface::TELEPHONE,
        CompanyInterface::COUNTRY_ID,
        CompanyInterface::SUPER_USER_ID,
        CompanyInterface::CUSTOMER_GROUP_ID
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
        foreach ($this->requiredFields as $field) {
            if (empty($this->company->getData($field))) {
                $this->exception->addError(
                    __(
                        '"%fieldName" is required. Enter and try again.',
                        ['fieldName' => $field]
                    )
                );
            }
        }
    }
}
