<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PrintAction
 */
class PrintAction extends \Magento\NegotiableQuote\Controller\Adminhtml\Quote implements HttpGetActionInterface
{
    /**
     * Print quote
     *
     * @return void|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');

        if ($quoteId) {
            try {
                $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
                $resultPage->addBreadcrumb(__('Sales'), __('Sales'));
                $resultPage->addBreadcrumb(__('Quotes'), __('Quotes'));
                $resultPage->getConfig()->getTitle()->prepend(__('Quote #%1', $quoteId));
                return $resultPage;
            } catch (NoSuchEntityException $e) {
                $this->addNotFoundError();
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addError(__('Exception occurred during quote print: %1', $e->getMessage()));
            }
        }
        $this->_forward('noroute');
    }
}
