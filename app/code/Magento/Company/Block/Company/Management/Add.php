<?php
namespace Magento\Company\Block\Company\Management;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Block for add new customer.
 *
 * @api
 * @since 100.0.0
 */
class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\RoleManagementInterface
     */
    private $roleManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Add constructor.
     *
     * @param Context $context
     * @param UserContextInterface $userContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Company\Api\RoleManagementInterface $roleManagement
     * @param array $data [optional]
     */
    public function __construct(
        Context $context,
        UserContextInterface $userContext,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Company\Api\RoleManagementInterface $roleManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->userContext = $userContext;
        $this->roleManagement = $roleManagement;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get roles.
     *
     * @return \Magento\Company\Api\Data\RoleInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRoles()
    {
        $customer = $this->customerRepository->getById($this->userContext->getUserId());
        $companyId = $customer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId();
        return $this->roleManagement->getRolesByCompanyId($companyId);
    }
}
