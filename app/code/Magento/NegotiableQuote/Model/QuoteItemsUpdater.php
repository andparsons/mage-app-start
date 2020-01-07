<?php
 declare(strict_types=1);

namespace Magento\NegotiableQuote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\NegotiableQuote\Model\Quote\TotalsFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class for updating quote items attributes and options for complex products.
 */
class QuoteItemsUpdater
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Quote
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    private $quote;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    private $cartFactory;

    /**
     * @var bool
     */
    private $hasChanges = false;

    /**
     * @var bool
     */
    private $hasUnconfirmedChanges = false;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\TotalsFactory
     */
    private $quoteTotalsFactory;

    /**
     * Json Serializer instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param Cart $cart
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param TotalsFactory $quoteTotalsFactory
     * @param Json|null $serialize
     */
    public function __construct(
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        Cart $cart,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        TotalsFactory $quoteTotalsFactory,
        Json $serialize = null
    ) {
        $this->negotiableQuoteHelper = $negotiableQuoteHelper;
        $this->cart = $cart;
        $this->resolver = $resolver;
        $this->cartFactory = $cartFactory;
        $this->quoteTotalsFactory = $quoteTotalsFactory;
        $this->serializer = $serialize ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Update quote items by $itemsData.
     *
     * @param CartInterface $quote
     * @param array $itemsData
     * @return bool
     */
    public function updateItemsForQuote(\Magento\Quote\Api\Data\CartInterface $quote, array $itemsData)
    {
        $this->quote = $quote;
        $items = [];
        $itemsForAdd = $this->getDataFromArray($itemsData, 'addItems');
        $itemsForUpdate = $this->getDataFromArray($itemsData, 'items');
        $configuredItemsAdd = $this->getDataFromArray($itemsData, 'configuredItems');

        $this->quote->setIsSuperMode(true);
        foreach ($itemsForUpdate as $data) {
            $item = $this->updateQuoteItem($data);
            if ($item !== null) {
                $items[(int)$data['id']] = $item;
            } else {
                $item = ['sku' => $data['sku'], 'productSku' => $data['productSku'], 'qty' => $data['qty']];
                if ($data['config']) {
                    $config = [];
                    // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    parse_str(urldecode($data['config']), $config);
                    $item['config'] = $config;
                    $configuredItemsAdd[] = $item;
                } else {
                    $itemsForAdd[] = $item;
                }
            }
        }

        $this->removeQuoteItemsNotInArray($items);
        $this->addConfiguredItems($configuredItemsAdd);
        $this->addItems($itemsForAdd);
        $this->quote->setIsSuperMode(false);
        $this->addRemovedSkus($quote, $itemsForUpdate);

        return $this->hasChanges;
    }

    /**
     * Getter for hasUnconfirmedChanges property.
     *
     * @return bool
     */
    public function hasUnconfirmedChanges()
    {
        return $this->hasUnconfirmedChanges;
    }

    /**
     * Get item data from array.
     *
     * @param array $itemsData
     * @param string $name
     * @return array
     */
    private function getDataFromArray(array $itemsData, $name)
    {
        return empty($itemsData[$name]) ? [] : $itemsData[$name];
    }

    /**
     * Update quote item by data.
     *
     * @param array $data
     * @return null|CartItemInterface
     */
    private function updateQuoteItem(array $data)
    {
        if (!isset($data['id']) || !isset($data['qty'])) {
            return null;
        }
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        $item = $this->quote->getItemById((int)$data['id']);
        if (!$item || $this->isNeedReconfigurationItem($item, $data)) {
            return null;
        }
        $item->clearMessage();
        $requestCountItems = (int)trim((string)$data['qty']);
        if (($requestCountItems > 0) && ($item->getQty() != $requestCountItems)) {
            $item->setQty($requestCountItems);
            $this->hasChanges = true;
        }
        return $item;
    }

    /**
     * Check if new and initial configuration of item are different.
     *
     * @param CartItemInterface $item
     * @param array $data
     * @return bool
     */
    private function isNeedReconfigurationItem(CartItemInterface $item, array $data)
    {
        if (!$item->getProduct()->canConfigure()) {
            return false;
        }

        $oldConfig = $this->negotiableQuoteHelper->retrieveCustomOptions($item, false);
        $newConfig = [];
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        parse_str(urldecode($data['config']), $newConfig);

        return is_array($oldConfig) && is_array($newConfig) && $oldConfig != $newConfig;
    }

    /**
     * Remove quote items from quote.
     *
     * @param array $items
     * @return void
     */
    private function removeQuoteItemsNotInArray(array $items)
    {
        foreach ($this->quote->getAllVisibleItems() as $item) {
            /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
            $itemId = $item->getId();
            if (empty($items[$itemId])) {
                $this->quote->removeItem($itemId);
                $this->hasChanges = true;
            }
        }
    }

    /**
     * Add configured complex products to quote.
     *
     * @param array $configuredItems
     * @return void
     */
    private function addConfiguredItems(array $configuredItems)
    {
        $result = $this->cart->addConfiguredItems($this->quote, $configuredItems);
        if ($result === true) {
            $this->hasChanges = true;
        }
    }

    /**
     * Add items to quote.
     *
     * @param array $addItems
     * @return void
     */
    private function addItems(array $addItems)
    {
        $result = $this->cart->addItems($this->quote, $addItems);
        if ($result === true) {
            $this->hasChanges = true;
        }
    }

    /**
     * Update quote by cart data.
     *
     * @param CartInterface $quote
     * @param array $cartData [optional]
     * @return CartInterface
     */
    public function updateQuoteItemsByCartData(\Magento\Quote\Api\Data\CartInterface $quote, array $cartData = [])
    {
        $totals = $this->quoteTotalsFactory->create(['quote' => $quote]);
        $quoteCatalogTotalPrice = $totals->getCatalogTotalPrice();
        $cartQty = (float)$quote->getItemsQty();

        $cartData = $this->processCartDataQty($cartData);

        // We instantiate a new cart model in order not to mess with the global one
        // We use it here as a helper to correctly set qty of quote items
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $this->cartFactory->create();
        $cart->setQuote($quote);
        $cartData = $cart->suggestItemsQty($cartData);
        $cart->updateItems($cartData);
        $quote = $cart->getQuote();

        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $value = $negotiableQuote->getNegotiatedPriceValue();
        if ($value !== null) {
            $negotiableQuote->setHasUnconfirmedChanges(true);
        }
        $quoteCatalogTotalPriceAfterRecalculation = $totals->getCatalogTotalPrice();
        $cartUpdatedQty = (float)$quote->getItemsQty();
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        if ($negotiableQuote->getNegotiatedPriceValue() !== null
            && ($quoteCatalogTotalPrice != $quoteCatalogTotalPriceAfterRecalculation
                || $cartQty != $cartUpdatedQty)
        ) {
            $negotiableQuote->setIsCustomerPriceChanged(true);
        }
        return $quote;
    }

    /**
     * Process cart data qty.
     *
     * @param array $cartData
     * @return array
     */
    private function processCartDataQty(array $cartData)
    {
        $filter = new \Zend_Filter_LocalizedToNormalized(
            [
                'locale' => $this->resolver->getLocale()
            ]
        );

        $qtyCache = [];
        foreach ($cartData as $index => $data) {
            if (isset($data['qty']) && trim((string)$data['qty']) !== '') {
                if (!isset($qtyCache[$data['qty']])) {
                    $qtyCache[$data['qty']] = $filter->filter(trim((string)$data['qty']));
                }
                $cartData[$index]['qty'] = $qtyCache[$data['qty']];
            }
        }

        return $cartData;
    }

    /**
     * Add removed products sku to quote.
     *
     * @param CartInterface $quote
     * @param array $items
     * @return void
     */
    private function addRemovedSkus(\Magento\Quote\Api\Data\CartInterface $quote, array $items)
    {
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $failedItems = $this->cart->getDeletedItemsSku();
        $productSkus = [];
        foreach ($items as $item) {
            if (in_array($item['sku'], $failedItems)) {
                $productSkus[] = $item['sku'];
            }
        }
        if ($productSkus) {
            $skus = $this->getSkuString($negotiableQuote->getDeletedSku(), $productSkus);
            $negotiableQuote->setDeletedSku($skus);
        }
    }

    /**
     * Get new skus serialize string.
     *
     * @param string $old
     * @param array $addSku
     * @return string
     */
    private function getSkuString($old, array $addSku)
    {
        if (empty($old)) {
            $arraySkus = [
                \Magento\Framework\App\Area::AREA_ADMINHTML => [],
                \Magento\Framework\App\Area::AREA_FRONTEND => []
            ];
        } else {
            $arraySkus = $this->serializer->unserialize($old);
        }
        $arraySkus[\Magento\Framework\App\Area::AREA_ADMINHTML] = array_unique(
            array_merge($arraySkus[\Magento\Framework\App\Area::AREA_ADMINHTML], $addSku)
        );
        return $this->serializer->serialize($arraySkus);
    }
}
