<?php

namespace Magento\Company\Model\SaveValidator;

/**
 * Checks if company email is valid.
 */
class CompanyEmail implements \Magento\Company\Model\SaveValidatorInterface
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
     * @var \Zend\Validator\EmailAddress
     */
    private $emailValidator;

    /**
     * @var \Magento\Company\Model\ResourceModel\Company\CollectionFactory
     */
    private $companyCollectionFactory;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $initialCompany;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\Framework\Exception\InputException $exception
     * @param \Zend\Validator\EmailAddress $emailValidator
     * @param \Magento\Company\Model\ResourceModel\Company\CollectionFactory $companyCollectionFactory
     * @param \Magento\Company\Api\Data\CompanyInterface $initialCompany
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\Framework\Exception\InputException $exception,
        \Zend\Validator\EmailAddress $emailValidator,
        \Magento\Company\Model\ResourceModel\Company\CollectionFactory $companyCollectionFactory,
        \Magento\Company\Api\Data\CompanyInterface $initialCompany
    ) {
        $this->company = $company;
        $this->exception = $exception;
        $this->emailValidator = $emailValidator;
        $this->companyCollectionFactory = $companyCollectionFactory;
        $this->initialCompany = $initialCompany;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!empty($this->company->getCompanyEmail())) {
            $isEmailAddress = $this->emailValidator->isValid($this->company->getCompanyEmail());

            if (!$isEmailAddress) {
                $this->exception->addError(
                    __(
                        'Invalid value of "%value" provided for the %fieldName field.',
                        ['fieldName' => 'company_email', 'value' => $this->company->getCompanyEmail()]
                    )
                );
            } elseif (!$this->company->getId()
                || $this->company->getCompanyEmail() != $this->initialCompany->getCompanyEmail()
            ) {
                /** @var \Magento\Company\Model\ResourceModel\Company\Collection $collection */
                $collection = $this->companyCollectionFactory->create();
                $collection->addFieldToFilter(
                    \Magento\Company\Api\Data\CompanyInterface::COMPANY_EMAIL,
                    $this->company->getCompanyEmail()
                )->load();
                if ($collection->getSize()) {
                    $this->exception->addError(
                        __(
                            'Company with this email address already exists in the system.'
                            . ' Enter a different email address to continue.'
                        )
                    );
                }
            }
        }
    }
}
