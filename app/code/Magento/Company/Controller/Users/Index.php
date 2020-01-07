<?php

namespace Magento\Company\Controller\Users;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Index.
 */
class Index extends \Magento\Company\Controller\AbstractAction implements HttpGetActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::users_view';

    /**
     * Company users.
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws \InvalidArgumentException
     */
    public function execute()
    {
        if ($this->companyContext->getCustomerId()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
            $resultPage->getConfig()->getTitle()->set(__('Company Users'));
        } else {
            $resultPage = $this->resultRedirectFactory->create()->setRefererUrl();
        }

        return $resultPage;
    }
}
