<?php

namespace Magento\NegotiableQuote\Controller\Adminhtml\Quote;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * Class AddConfigured
 */
class AddConfigured extends Update implements HttpPostActionInterface
{
    /**
     * Update quote items
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');

        try {
            $updateData = (array)\Zend\Json\Json::decode(
                $this->getRequest()->getParam('dataSend'),
                \Zend\Json\Json::TYPE_ARRAY
            );
            $this->quoteData = isset($updateData['quote']) ? $updateData['quote'] : [];
            $this->quoteData['configuredItems'] = $this->getConfigurableItems();
            $this->quoteCurrency->updateQuoteCurrency($quoteId);
            $this->quoteUpdater->updateQuote($quoteId, $this->quoteData);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addError(__('Something went wrong'));
        }
        $data = $this->getQuoteData();
        /** @var \Magento\Framework\Controller\Result\Json $response */
        $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $response->setJsonData(json_encode($data, JSON_NUMERIC_CHECK));

        return $response;
    }

    /**
     * Retrieve configurable items data from request
     *
     * @return null|array
     */
    private function getConfigurableItems()
    {
        $configuredItems = $this->getRequest()->getParam(
            \Magento\NegotiableQuote\Block\Adminhtml\AdvancedCheckout\Sales\Order\Create\Sku\Add::LIST_TYPE,
            []
        );
        $configuredItemsParams = (array)$this->getRequest()->getParam('item', []);
        foreach ($configuredItems as $id => &$item) {
            $item['config'] = isset($configuredItemsParams[$id]) ? $configuredItemsParams[$id] : [];
        }
        return $configuredItems;
    }
}
