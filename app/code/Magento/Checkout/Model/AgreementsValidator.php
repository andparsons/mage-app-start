<?php
namespace Magento\Checkout\Model;

/**
 * Class AgreementsValidator
 */
class AgreementsValidator implements \Magento\Checkout\Api\AgreementsValidatorInterface
{
    /**
     * Default validator
     *
     * @param int[] $agreementIds
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function isValid($agreementIds = [])
    {
        return true;
    }
}
