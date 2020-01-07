<?php

namespace Magento\Company\Model\Customer;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface PermissionInterface
 */
interface PermissionInterface
{
    /**
     * Is checkout allowed.
     *
     * @param CustomerInterface $customer
     * @param bool $isNegotiableQuoteActive
     * @return bool
     */
    public function isCheckoutAllowed(
        CustomerInterface $customer,
        $isNegotiableQuoteActive = false
    );

    /**
     * Is customer company blocked.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function isCompanyBlocked(CustomerInterface $customer);

    /**
     * Is login allowed.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    public function isLoginAllowed(CustomerInterface $customer);
}
