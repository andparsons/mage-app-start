<?php
namespace Magento\Sales\Model\Order\Payment\State;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Interface CommandInterface using for payment related changes of order state
 */
interface CommandInterface
{
    /**
     * Run command
     *
     * @param OrderPaymentInterface $payment
     * @param string|float|int $amount
     * @param OrderInterface $order
     * @return string
     */
    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order);
}
