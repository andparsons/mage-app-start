<?php
declare(strict_types=1);

namespace Magento\DataServices\ViewModel\Checkout;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\DataServices\Model\CartContextInterface;

/**
 * ViewModel for Checkout Context
 */
class CheckoutContextProvider implements ArgumentInterface
{
    /**
     * @var CartContextInterface
     */
    private $cartContext;

    /**
     * @param CartContextInterface $cartContext
     */
    public function __construct(
        CartContextInterface $cartContext
    ) {
        $this->cartContext = $cartContext;
    }

    /**
     * Return cart context for data layer
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getModelContext() : array
    {
        return $this->cartContext->getContextData();
    }
}
