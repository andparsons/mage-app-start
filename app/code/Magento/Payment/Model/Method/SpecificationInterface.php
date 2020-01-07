<?php
namespace Magento\Payment\Model\Method;

/**
 * Interface SpecificationInterface
 *
 * @api
 * @since 100.0.2
 */
interface SpecificationInterface
{
    /**
     * Check specification is satisfied by payment method
     *
     * @param string $paymentMethod
     * @return bool
     */
    public function isSatisfiedBy($paymentMethod);
}
