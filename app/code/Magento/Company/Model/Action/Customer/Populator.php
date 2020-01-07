<?php
namespace Magento\Company\Model\Action\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class for populating customer object.
 */
class Populator
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param DataObjectHelper $objectHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        DataObjectHelper $objectHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->objectHelper = $objectHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Populate customer.
     *
     * @param array $data
     * @param CustomerInterface $customer [optional]
     * @return CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function populate(array $data, CustomerInterface $customer = null)
    {
        if ($customer === null) {
            $customer = $this->customerFactory->create();
        }

        $customerId = $customer->getId();
        $this->objectHelper->populateWithArray(
            $customer,
            $data,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customer->setWebsiteId($this->storeManager->getWebsite()->getId());
        $customer->setId($customerId);

        return $customer;
    }
}
