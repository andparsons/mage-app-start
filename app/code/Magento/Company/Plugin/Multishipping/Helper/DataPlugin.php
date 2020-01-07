<?php
namespace Magento\Company\Plugin\Multishipping\Helper;

use Magento\Multishipping\Helper\Data;

/**
 * Class DataPlugin
 */
class DataPlugin
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Company\Model\Customer\PermissionInterface
     */
    private $permission;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\Customer\PermissionInterface $permission
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\Customer\PermissionInterface $permission
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->permission = $permission;
    }

    /**
     * After isMultishippingCheckout plugin.
     *
     * @param Data $subject
     * @param bool $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMultishippingCheckoutAvailable(
        Data $subject,
        $result
    ) {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $result = $result && $this->permission->isCheckoutAllowed($customer);
        }

        return $result;
    }
}
