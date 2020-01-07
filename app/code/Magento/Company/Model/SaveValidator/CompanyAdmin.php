<?php

namespace Magento\Company\Model\SaveValidator;

/**
 * Checks if company has a valid customer as a company admin.
 */
class CompanyAdmin implements \Magento\Company\Model\SaveValidatorInterface
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
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Company\Api\Data\CompanyInterface $company
     * @param \Magento\Framework\Exception\InputException $exception
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Company\Api\Data\CompanyInterface $company,
        \Magento\Framework\Exception\InputException $exception,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->company = $company;
        $this->exception = $exception;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!empty($this->company->getSuperUserId())) {
            $customer = $this->customerRepository->getById($this->company->getSuperUserId());
            if ($customer->getExtensionAttributes() !== null
                && $customer->getExtensionAttributes()->getCompanyAttributes() !== null
            ) {
                $status = $customer->getExtensionAttributes()->getCompanyAttributes()->getStatus();
                if (!$status) {
                    $this->exception->addError(__(
                        'The selected user is inactive.'
                        . ' To continue, select another user or activate the current user.'
                    ));
                }
                $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
                if ($companyId && $companyId != $this->company->getId()) {
                    $this->exception->addError(__(
                        'This customer is a user of a different company.'
                        . ' Enter a different email address to continue.'
                    ));
                }
            }
        }
    }
}
