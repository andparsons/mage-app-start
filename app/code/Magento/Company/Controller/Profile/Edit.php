<?php
namespace Magento\Company\Controller\Profile;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Edit
 */
class Edit extends \Magento\Company\Controller\AbstractAction implements HttpGetActionInterface
{
    /**
     * Edit company profile form
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Company Profile'));

        return $resultPage;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return $this->companyContext->isResourceAllowed('Magento_Company::edit_account')
        || $this->companyContext->isResourceAllowed('Magento_Company::edit_address');
    }
}
