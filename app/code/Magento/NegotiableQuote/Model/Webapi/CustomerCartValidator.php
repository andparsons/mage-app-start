<?php

namespace Magento\NegotiableQuote\Model\Webapi;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class for checking if user has access to cart.
 */
class CustomerCartValidator
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param UserContextInterface $userContext
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->userContext = $userContext;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Checks if user has access to cart.
     *
     * @param int $cartId
     * @return void
     * @throws SecurityViolationException
     */
    public function validate($cartId)
    {
        if ($this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER) {
            $quote = $this->quoteRepository->get($cartId);
            if ($quote->getCustomer()->getId() == $this->userContext->getUserId()) {
                return;
            }
        }
        throw new SecurityViolationException(__('You are not allowed to do this.'));
    }
}
