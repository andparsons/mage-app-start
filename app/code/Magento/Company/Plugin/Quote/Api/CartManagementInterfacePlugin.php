<?php

namespace Magento\Company\Plugin\Quote\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;

/**
 * Class CartManagementInterfacePlugin
 */
class CartManagementInterfacePlugin
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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\Customer\PermissionInterface $permission
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\Customer\PermissionInterface $permission,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->permission = $permission;
        $this->request = $request;
    }

    /**
     * Before placeOrder plugin.
     *
     * @param CartManagementInterface $subject
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface|null $paymentMethod
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforePlaceOrder(
        CartManagementInterface $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod = null
    ) {
        $customerId = $this->userContext->getUserId();
        $userType = $this->userContext->getUserType();
        if ($customerId && $userType == \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER) {
            $customer = $this->customerRepository->getById($customerId);
            $isNegotiableQuote = (bool)$this->request->getParam('isNegotiableQuote');
            if (!$this->permission->isCheckoutAllowed($customer, $isNegotiableQuote)) {
                throw new LocalizedException(
                    __('This customer company account is blocked and customer cannot place orders.')
                );
            }
        }

        return [$cartId, $paymentMethod];
    }
}
