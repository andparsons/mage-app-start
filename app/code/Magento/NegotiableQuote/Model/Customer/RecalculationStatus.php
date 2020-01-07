<?php

namespace Magento\NegotiableQuote\Model\Customer;

use Magento\Customer\Api\Data\AddressInterface;

/**
 * Status for quotes recalculation based on attached shipping addresses.
 */
class RecalculationStatus
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\CollectionFactory
     */
    private $addressCollectionFactory;

    /**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollectionFactory
    ) {
        $this->taxHelper = $taxHelper;
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    /**
     * Check if address has changed and negotiable quote should be recalculated.
     *
     * @param AddressInterface $address
     * @return bool
     */
    public function isNeedRecalculate(AddressInterface $address)
    {
        if (!$address || $this->taxHelper->getTaxBasedOn() !== 'origin') {
            return false;
        }
        $collection = $this->addressCollectionFactory->create();
        $collection->addFilter(AddressInterface::ID, $address->getId());
        /** @var AddressInterface $oldAddress */
        $oldAddress = $collection->getItemById($address->getId());

        return $oldAddress->getRegionId() !== $address->getRegionId()
            || $oldAddress->getCountryId() !== $address->getCountryId()
            || $oldAddress->getPostcode() !== $address->getPostcode();
    }
}
