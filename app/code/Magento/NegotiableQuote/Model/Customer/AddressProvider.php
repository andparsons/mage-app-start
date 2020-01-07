<?php

namespace Magento\NegotiableQuote\Model\Customer;

/**
 * Class AddressProvider provides address details for owner of the quote.
 */
class AddressProvider
{
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private $customer;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * AddressProvider constructor.
     *
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressConfig = $addressConfig;
        $this->customer = $customer;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Get all customer addresses.
     *
     * @return array
     */
    public function getAllCustomerAddresses()
    {
        try {
            $customerId = $this->customer->getId();
            $filter = [
                $this->filterBuilder->setField('parent_id')
                    ->setValue($customerId)
                    ->setConditionType('eq')
                    ->create()
            ];

            $searchCriteria = $this->searchCriteriaBuilder->addFilters($filter)->create();
            $addresses = (array)($this->addressRepository->getList($searchCriteria)->getItems());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $addresses = [];
        }

        return $addresses;
    }

    /**
     * Get rendered address.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return string
     */
    public function getRenderedAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $flatAddressArray = $this->extensibleDataObjectConverter->toFlatArray(
            $address,
            [],
            \Magento\Quote\Api\Data\AddressInterface::class
        );
        $flatAddressArray = $this->getFlatAddress($address, $flatAddressArray);

        if (!empty($flatAddressArray[\Magento\Quote\Api\Data\AddressInterface::KEY_POSTCODE])) {
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($flatAddressArray);
        }

        return '';
    }

    /**
     * Get rendered address line.
     *
     * @param int $addressId
     * @return string
     */
    public function getRenderedLineAddress($addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);
            $flatAddressArray = $this->extensibleDataObjectConverter->toFlatArray(
                $address,
                [],
                \Magento\Customer\Api\Data\AddressInterface::class
            );
            $flatAddressArray = $this->getFlatAddress($address, $flatAddressArray);
            /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('oneline')->getRenderer();
            $result = $renderer->renderArray($flatAddressArray);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = '';
        }

        return $result;
    }

    /**
     * Get flat address.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface|\Magento\Customer\Api\Data\AddressInterface $address
     * @param array $flatAddressArray
     * @return array
     */
    private function getFlatAddress($address, array $flatAddressArray)
    {
        $street = $address->getStreet();

        if (!empty($street) && is_array($street)) {
            // Unset flat street data
            $streetKeys = array_keys($street);
            foreach ($streetKeys as $key) {
                if (is_array($flatAddressArray)) {
                    unset($flatAddressArray[$key]);
                }
            }
            //Restore street as an array
            $flatAddressArray['street'] = $street;
        }

        return $flatAddressArray;
    }
}
