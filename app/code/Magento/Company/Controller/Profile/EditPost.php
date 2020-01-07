<?php
namespace Magento\Company\Controller\Profile;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Controller for saving company profile.
 */
class EditPost extends \Magento\Company\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Company\Model\CompanyProfile
     */
    private $companyProfile;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Company\Model\CompanyProfile $companyProfile
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Company\Model\CompanyProfile $companyProfile,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        parent::__construct($context, $companyContext, $logger);
        $this->companyManagement = $companyManagement;
        $this->formKeyValidator = $formKeyValidator;
        $this->companyProfile = $companyProfile;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Edit company profile form.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $request = $this->getRequest();
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/profile/');

        if ($request->isPost()) {
            if (!$this->formKeyValidator->validate($request)) {
                return $resultRedirect;
            }

            try {
                $customerId = $this->companyContext->getCustomerId();

                if ($customerId) {
                    $company = $this->companyManagement->getByCustomerId($customerId);

                    if ($company && $company->getId()) {
                        $postData = $request->getParams();
                        $this->companyProfile->populate($company, $postData);
                        $this->companyRepository->save($company);
                        $this->messageManager->addSuccess(
                            __('The changes you made on the company profile have been successfully saved.')
                        );
                        return $resultRedirect;
                    }
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(__('You must fill in all required fields before you can continue.'));
                $this->logger->critical($e);
                return $resultRedirect->setPath('*/profile/edit');
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred on the server. Your changes have not been saved.')
                );
                $this->logger->critical($e);
                return $resultRedirect->setPath('*/profile/edit');
            }
        }

        return $resultRedirect;
    }

    /**
     * @inheritdoc
     */
    public function isAllowed()
    {
        return $this->companyContext->isResourceAllowed('Magento_Company::edit_account')
        || $this->companyContext->isResourceAllowed('Magento_Company::edit_address');
    }
}
