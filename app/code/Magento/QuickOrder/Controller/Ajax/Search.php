<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\QuickOrder\Controller\Ajax;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use \Magento\Framework\App\Action\Context;
use Magento\QuickOrder\Model\Config as ModuleConfig;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\QuickOrder\Model\Cart;

class Search extends \Magento\QuickOrder\Controller\AbstractAction implements HttpPostActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @param Context $context
     * @param ModuleConfig $moduleConfig
     * @param JsonFactory $resultJsonFactory
     * @param Cart $cart
     */
    public function __construct(
        Context $context,
        ModuleConfig $moduleConfig,
        JsonFactory $resultJsonFactory,
        Cart $cart
    ) {
        parent::__construct($context, $moduleConfig);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->cart = $cart;
    }

    /**
     * Get info about products, which SKU specified in request
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $requestData = $this->getRequest()->getPostValue();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $generalErrorMessage = '';

        $items = json_decode($requestData['items'], true);
        $items = $this->removeEmptySkuItems($items);
        if (empty($items)) {
            $generalErrorMessage = __(
                'The uploaded CSV file does not contain a column labelled SKU. ' .
                'Make sure the first column is labelled SKU and that each line in the file contains a SKU value. ' .
                'Then upload the file again.'
            );
        } else {
            $this->cart->setContext(Cart::CONTEXT_FRONTEND);
            $this->cart->prepareAddProductsBySku($items);
            $items = $this->cart->getAffectedItems();
        }

        $data = [
            'generalErrorMessage' => (string) $generalErrorMessage,
            'items' => $items
        ];

        return $resultJson->setData($data);
    }

    /**
     * @param array $items
     * @return array
     */
    protected function removeEmptySkuItems(array $items)
    {
        foreach ($items as $k => $item) {
            if (empty($item['sku'])) {
                unset($items[$k]);
            }
        }

        return $items;
    }
}
