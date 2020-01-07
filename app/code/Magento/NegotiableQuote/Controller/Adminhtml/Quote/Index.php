<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\NegotiableQuote\Controller\Adminhtml\Quote;

/**
 * Class Index
 */
class Index extends Quote implements HttpGetActionInterface
{
    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $resultPage = $this->initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Quotes'));
        return $resultPage;
    }
}
