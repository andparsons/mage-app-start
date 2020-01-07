<?php
namespace Magento\Company\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\Company\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * New company action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Company::company_index');
        $resultPage->getConfig()->getTitle()->prepend(__('New Company'));
        return $resultPage;
    }
}
