<?php
namespace Magento\Company\Controller\Role;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class EditPost.
 */
class EditPost extends \Magento\Company\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::roles_edit';

    /**
     * @var \Magento\Company\Api\RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var \Magento\Company\Api\Data\RoleInterfaceFactory
     */
    private $roleFactory;

    /**
     * @var \Magento\Company\Model\CompanyUser
     */
    private $companyUser;

    /**
     * @var \Magento\Company\Model\PermissionManagementInterface
     */
    private $permissionManagement;

    /**
     * EditPost constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\RoleRepositoryInterface $roleRepository
     * @param \Magento\Company\Api\Data\RoleInterfaceFactory $roleFactory
     * @param \Magento\Company\Model\CompanyUser $companyUser
     * @param \Magento\Company\Model\PermissionManagementInterface $permissionManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Api\RoleRepositoryInterface $roleRepository,
        \Magento\Company\Api\Data\RoleInterfaceFactory $roleFactory,
        \Magento\Company\Model\CompanyUser $companyUser,
        \Magento\Company\Model\PermissionManagementInterface $permissionManagement
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->roleRepository = $roleRepository;
        $this->roleFactory = $roleFactory;
        $this->companyUser = $companyUser;
        $this->permissionManagement = $permissionManagement;
    }

    /**
     * Roles and permissions edit post.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $role = $this->roleFactory->create();
        $id = $this->getRequest()->getParam('id');

        try {
            $companyId = $this->companyUser->getCurrentCompanyId();
            if ($id) {
                $role = $this->roleRepository->get($id);
                if ($role->getCompanyId() != $companyId) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Bad Request'));
                }
            }
            $role->setRoleName($this->getRequest()->getParam('role_name'));
            $role->setCompanyId($companyId);
            $resources = explode(',', $this->getRequest()->getParam('role_permissions'));
            $role->setPermissions($this->permissionManagement->populatePermissions($resources));
            $this->roleRepository->save($role);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e);

            if ($id) {
                $result = $this->_redirect('*/role/edit', ['id' => $id]);
            } else {
                $result = $this->_redirect('*/role/edit');
            }
            return $result;
        }
        return $this->_redirect('*/*/');
    }
}
