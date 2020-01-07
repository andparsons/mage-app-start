<?php

namespace Magento\NegotiableQuote\Model\Webapi\Quote;

use Magento\NegotiableQuote\Api\CouponManagementInterface;

/**
 * Class CouponManagement
 */
class CouponManagement implements CouponManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CouponManagementInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @param \Magento\Quote\Api\CouponManagementInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     */
    public function __construct(
        \Magento\Quote\Api\CouponManagementInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function set($cartId, $couponCode)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->set($cartId, $couponCode);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($cartId)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->remove($cartId);
    }
}
