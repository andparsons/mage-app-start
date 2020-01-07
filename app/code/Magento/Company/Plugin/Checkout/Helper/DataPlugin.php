<?php
namespace Magento\Company\Plugin\Checkout\Helper;

use Magento\Checkout\Helper\Data;

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
     * After canOnepageCheckout plugin.
     *
     * @param Data $subject
     * @param bool $result
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanOnepageCheckout(
        Data $subject,
        $result
    ) {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $isNegotiableQuoteActive = (bool) $this->request->getParam('negotiableQuoteId');
            $result = $result &&
                      $this->permission->isCheckoutAllowed($customer, $isNegotiableQuoteActive);
        }

        return $result;
    }
}
