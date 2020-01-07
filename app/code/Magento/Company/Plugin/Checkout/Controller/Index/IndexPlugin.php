<?php
namespace Magento\Company\Plugin\Checkout\Controller\Index;

use Magento\Checkout\Controller\Index\Index;

/**
 * Class IndexPlugin.
 */
class IndexPlugin
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
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission
     */
    private $companyUserPermission;

    /**
     * @param \Magento\Authorization\Model\UserContextInterface $userContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Model\Customer\PermissionInterface $permission
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\Company\Model\CompanyUserPermission $companyUserPermission
     */
    public function __construct(
        \Magento\Authorization\Model\UserContextInterface $userContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Model\Customer\PermissionInterface $permission,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\Company\Model\CompanyUserPermission $companyUserPermission
    ) {
        $this->userContext = $userContext;
        $this->customerRepository = $customerRepository;
        $this->permission = $permission;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->authorization = $authorization;
        $this->companyUserPermission = $companyUserPermission;
    }

    /**
     * Checkout around execute plugin.
     *
     * @param Index $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        Index $subject,
        \Closure $proceed
    ) {
        $customerId = $this->userContext->getUserId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $isNegotiableQuoteActive = (bool) $this->request->getParam('negotiableQuoteId');
            if (!$this->permission->isCheckoutAllowed($customer, $isNegotiableQuoteActive)) {
                $resultRedirect = $this->resultRedirectFactory->create();

                if ($this->companyUserPermission->isCurrentUserCompanyUser()) {
                    $resultRedirect->setPath('company/accessdenied');
                } else {
                    $resultRedirect->setPath('noroute');
                }

                return $resultRedirect;
            }
        }

        return $proceed();
    }
}
