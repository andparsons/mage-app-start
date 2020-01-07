<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\NegotiableQuote\Helper\Company as CompanyHelper;

/**
 * Validator for customer from quote.
 */
class Customer implements ValidatorInterface
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company
     */
    private $companyHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @param CompanyManagementInterface $companyManagement
     * @param CompanyHelper $companyHelper
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        CompanyManagementInterface $companyManagement,
        CompanyHelper $companyHelper,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
    ) {
        $this->companyManagement = $companyManagement;
        $this->companyHelper = $companyHelper;
        $this->validatorResultFactory = $validatorResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $result = $this->validatorResultFactory->create();
        if (empty($data['quote'])) {
            return $result;
        }
        $quote = $data['quote'];
        $customer = $quote->getCustomer();
        if (!$customer->getExtensionAttributes()
            || !$customer->getExtensionAttributes()->getCompanyAttributes()
            || !$customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        ) {
            $result->addMessage(
                __(
                    'Invalid attribute %fieldName: Cannot create a B2B Quote for an individual user. '
                    . 'The user must be a company member. Row ID: %fieldName = %fieldValue',
                    ['fieldName' => 'quoteId', 'fieldValue' => $quote->getId()]
                )
            );
            return $result;
        }

        $company = $this->companyManagement->getByCustomerId($customer->getId());
        $quoteConfig = $this->companyHelper->getQuoteConfig($company);
        if (!$quoteConfig->getIsQuoteEnabled() ||
            $customer->getExtensionAttributes()->getCompanyAttributes()->getStatus()
            != CompanyInterface::STATUS_APPROVED
        ) {
            $result->addMessage(
                __(
                    'Invalid attribute %fieldName: Quoting is not allowed for this company. '
                    . 'Row ID: %fieldName = %fieldValue',
                    ['fieldName' => 'quoteId', 'fieldValue' => $quote->getId()]
                )
            );
            return $result;
        }

        return $result;
    }
}
