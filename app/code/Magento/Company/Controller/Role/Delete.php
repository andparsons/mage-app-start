<?php
namespace Magento\Company\Controller\Role;

/**
 * Controller for role deleting.
 */
class Delete extends \Magento\Company\Controller\AbstractAction
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
     * @var \Magento\Company\Model\CompanyUser
     */
    private $companyUser;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\RoleRepositoryInterface $roleRepository
     * @param \Magento\Company\Model\CompanyUser $companyUser
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Api\RoleRepositoryInterface $roleRepository,
        \Magento\Company\Model\CompanyUser $companyUser
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->roleRepository = $roleRepository;
        $this->companyUser = $companyUser;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $request = $this->getRequest();
        $roleId = $request->getParam('id');
        try {
            $role = $this->roleRepository->get($roleId);
            $companyId = $this->companyUser->getCurrentCompanyId();

            if ($role->getCompanyId() != $companyId) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Bad Request'));
            }

            $this->roleRepository->delete($role->getId());
            $this->messageManager->addSuccessMessage(
                __(
                    'You have deleted role %companyRoleName.',
                    ['companyRoleName' => $role ? $role->getRoleName() : '']
                )
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
            $this->logger->critical($e);
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('company/role/');

        return $resultRedirect;
    }
}
