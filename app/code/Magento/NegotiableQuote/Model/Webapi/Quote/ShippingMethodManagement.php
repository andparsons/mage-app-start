<?php

namespace Magento\NegotiableQuote\Model\Webapi\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Api\ShippingMethodManagementInterface;

/**
 * Class ShippingMethodManagement
 */
class ShippingMethodManagement implements ShippingMethodManagementInterface
{
    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     */
    public function __construct(
        \Magento\Quote\Api\ShippingMethodManagementInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        try {
            $this->validator->validate($cartId);
            return $this->originalInterface->estimateByAddressId($cartId, $addressId);
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}
