<?php
declare(strict_types=1);

namespace Magento\DataServices\ViewModel\Checkout;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;

/**
 * ViewModel for Multishipping Checkout Success Context
 */
class MultishippingSuccessContextProvider implements ArgumentInterface
{
    /**
     * @var Multishipping
     */
    private $multishipping;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Multishipping $multishipping
     * @param CustomerSession $customerSession
     */
    public function __construct(
        Multishipping $multishipping,
        CustomerSession $customerSession
    ) {
        $this->multishipping = $multishipping;
        $this->customerSession = $customerSession;
    }

    /**
     * Return cart id for event tracking
     *
     * @return int
     */
    public function getCartId() : int
    {
        $cartId = $this->customerSession->getDataServicesCartId();
        $this->customerSession->unsDataServicesCartId();
        return $cartId;
    }

    /**
     * Return order ids for event tracking
     *
     * @return string
     */
    public function getOrderId() : string
    {
        return implode(',', $this->multishipping->getOrderIds());
    }
}
