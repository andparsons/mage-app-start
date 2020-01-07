<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Controller for Company edit page.
 */
class Edit extends \Magento\Company\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_publicActions = ['edit'];

    /**
     * Edit company action.
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $companyId = $this->getRequest()->getParam('id');
        try {
            $company = $this->companyRepository->get($companyId);
            $resultPage->setActiveMenu('Magento_Company::company_index');
            $resultPage->getConfig()->getTitle()->prepend($company->getCompanyName());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('The requested company is not found'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
        return $resultPage;
    }
}
