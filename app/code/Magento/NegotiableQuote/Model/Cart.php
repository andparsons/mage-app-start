<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Model;

use Magento\AdvancedCheckout\Model\CartFactory;

/**
 * Class for update quotes
 */
class Cart
{
    /** @var CartFactory  */
    private $cartFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionQuote;

    /**
     * Constructor
     *
     * @param CartFactory $cartFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionQuote
     */
    public function __construct(
        CartFactory $cartFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionQuote
    ) {
        $this->cartFactory = $cartFactory;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * Remove failed sku from quote items
     *
     * @param string $sku
     * @return void
     */
    public function removeFailedSku($sku)
    {
        /** @var \Magento\AdvancedCheckout\Model\Cart $cart */
        $cart = $this->cartFactory->create();
        $cart->setSession($this->sessionQuote);
        $cart->removeAffectedItem($sku);
    }

    /**
     * Remove all failed skus from quote items
     *
     * @return void
     */
    public function removeAllFailed()
    {
        /** @var \Magento\AdvancedCheckout\Model\Cart $cart */
        $cart = $this->cartFactory->create();
        $cart->setSession($this->sessionQuote);
        $cart->removeAllAffectedItems();
    }

    /**
     * Add configured composite products to quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param array $configuredItems
     * @return bool
     */
    public function addConfiguredItems(\Magento\Quote\Api\Data\CartInterface $quote, array $configuredItems)
    {
        /** @var \Magento\AdvancedCheckout\Model\Cart $cart */
        $cart = $this->cartFactory->create();
        $cart->setQuote($quote);
        $cart->setContext(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_ADMIN_CHECKOUT);
        $hasChanges = false;

        if (!$configuredItems) {
            return $hasChanges;
        }
        foreach ($configuredItems as $id => $params) {
            $sku = (string) (isset($params['productSku']) ? $params['productSku'] : $id);
            $cart->removeAffectedItem($sku);
            $cart->prepareAddProductBySku(
                $sku,
                $params['qty'],
                $params['config']
            );
            $hasChanges = true;
            $cart->saveAffectedProducts($cart, false);
        }
        $cart->saveAffectedProducts($cart, false);

        return $hasChanges;
    }

    /**
     * Add items
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param array $addItems
     * @return bool
     */
    public function addItems(\Magento\Quote\Api\Data\CartInterface $quote, array $addItems)
    {
        $hasChanges = false;
        if (!empty($addItems)) {
            /** @var \Magento\AdvancedCheckout\Model\Cart $cart */
            $cart = $this->cartFactory->create();

            $cart->setQuote($quote);
            $cart->setContext(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_ADMIN_CHECKOUT);
            $cart->prepareAddProductsBySku($addItems);
            $cart->saveAffectedProducts($cart, false);
            $hasChanges = $this->isAllItemsFailed($cart, $addItems);
        }

        return $hasChanges;
    }

    /**
     * Check if all items added by sku are failed to be added.
     *
     * @param \Magento\AdvancedCheckout\Model\Cart $cart
     * @param array $addItems
     * @return bool
     */
    private function isAllItemsFailed(\Magento\AdvancedCheckout\Model\Cart $cart, array $addItems)
    {
        $hasChanges = true;
        $failedItems = $cart->getFailedItems();
        $failedItemsSkus = [];
        foreach ($failedItems as $failedItem) {
            if (isset($failedItem['item']['sku'])) {
                $failedItemsSkus[] = $failedItem['item']['sku'];
            }
        }

        $isOrigItemsAdded = false;
        foreach ($addItems as $addItem) {
            if (isset($addItem['sku'])
                && !in_array($addItem['sku'], $failedItemsSkus)) {
                $isOrigItemsAdded = true;
            }
        }

        if (!$isOrigItemsAdded) {
            $hasChanges = false;
        }

        return $hasChanges;
    }

    /**
     * Get failed items sku
     *
     * @return array
     */
    public function getDeletedItemsSku()
    {
        /** @var \Magento\AdvancedCheckout\Model\Cart $cart */
        $cart = $this->cartFactory->create();
        $cart->setSession($this->sessionQuote);
        $skus = [];
        foreach ($cart->getFailedItems() as $item) {
            if ($item['code'] == \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU) {
                $skus[] = $item['item']['sku'];
            }
        }
        return $skus;
    }
}
