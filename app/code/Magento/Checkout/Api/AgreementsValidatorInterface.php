<?php
namespace Magento\Checkout\Api;

/**
 * Interface AgreementsValidatorInterface
 * @api
 * @since 100.0.2
 */
interface AgreementsValidatorInterface
{
    /**
     * @param array $agreementIds
     * @return bool
     */
    public function isValid($agreementIds = []);
}
