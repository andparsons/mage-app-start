<?php

namespace Magento\NegotiableQuote\Model\Webapi\Quote;

use Magento\NegotiableQuote\Api\CartTotalRepositoryInterface;

/**
 * Class CartTotalRepository
 */
class CartTotalRepository implements CartTotalRepositoryInterface
{
    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     */
    public function __construct(
        \Magento\Quote\Api\CartTotalRepositoryInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->get($cartId);
    }
}
