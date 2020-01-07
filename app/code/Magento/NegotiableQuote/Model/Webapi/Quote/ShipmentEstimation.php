<?php

namespace Magento\NegotiableQuote\Model\Webapi\Quote;

use Magento\NegotiableQuote\Api\ShipmentEstimationInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Class ShipmentEstimation
 */
class ShipmentEstimation implements ShipmentEstimationInterface
{
    /**
     * @var \Magento\Quote\Api\ShipmentEstimationInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @param \Magento\Quote\Api\ShipmentEstimationInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     */
    public function __construct(
        \Magento\Quote\Api\ShipmentEstimationInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->estimateByExtendedAddress($cartId, $address);
    }
}
