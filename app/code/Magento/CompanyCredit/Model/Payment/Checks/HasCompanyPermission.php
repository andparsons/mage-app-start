<?php

namespace Magento\CompanyCredit\Model\Payment\Checks;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

/**
 * Class HasPermission.
 */
class HasCompanyPermission implements \Magento\Payment\Model\Checks\SpecificationInterface
{
    /**
     * Payment on Account method code.
     */
    const PAYMENT_ACCOUNT_METHOD_CODE = 'companycredit';

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * HasPermission constructor.
     *
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Company\Api\AuthorizationInterface $authorization
    ) {
        $this->userContext = $userContext;
        $this->authorization = $authorization;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        if (!$quote->getCustomerId()) {
            return true;
        }

        if ($paymentMethod->getCode() == self::PAYMENT_ACCOUNT_METHOD_CODE
            && $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
        ) {
            return $this->authorization->isAllowed('Magento_Sales::payment_account');
        }

        return true;
    }
}
