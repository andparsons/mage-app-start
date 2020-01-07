<?php
namespace Magento\Cybersource\Gateway\Validator\Soap;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class SubscriptionIdValidator
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.3 Cybersource payment method core integration is deprecated
 *      in favor of official payment integration available on the marketplace
 */
class SubscriptionIdValidator extends AbstractValidator
{
    /**
     * Performs domain-related validation for business object
     *
     * @param array $validationSubject
     * @return null|ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = SubjectReader::readResponse($validationSubject);

        $result = $this->createResult(
            isset($response['paySubscriptionCreateReply']['subscriptionID']),
            [__('Your payment has been declined. Please try again.')]
        );

        return $result;
    }
}
