<?php
declare(strict_types=1);

namespace Magento\DataServices\ViewModel\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Checkout Success Context
 */
class SuccessContextProvider implements ArgumentInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Return cart id for event tracking
     *
     * @return int
     */
    public function getCartId() : int
    {
        return (int) $this->checkoutSession->getLastRealOrder()->getQuoteId();
    }

    /**
     * Return order id for event tracking
     *
     * @return string
     */
    public function getOrderId() : string
    {
        return $this->checkoutSession->getLastRealOrder()->getIncrementId();
    }
}
