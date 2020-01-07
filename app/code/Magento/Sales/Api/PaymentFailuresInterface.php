<?php
declare(strict_types=1);

namespace Magento\Sales\Api;

/**
 * Interface for managing payment gateway failures.
 */
interface PaymentFailuresInterface
{
    /**
     * Handles payment gateway failures.
     *
     * @param int $cartId
     * @param string $errorMessage
     * @param string $checkoutType
     * @return PaymentFailuresInterface
     */
    public function handle(
        int $cartId,
        string $errorMessage,
        string $checkoutType = 'onepage'
    ): PaymentFailuresInterface;
}
