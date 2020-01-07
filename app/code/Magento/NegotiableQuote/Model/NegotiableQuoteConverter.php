<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;

/**
 * Class for convert form negotiable quote to snapshot and vice versa.
 */
class NegotiableQuoteConverter
{
    /**
     * @var \Magento\Quote\Api\Data\CartInterfaceFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    protected $cartItemFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterfaceFactory
     */
    protected $negotiableQuoteItemFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory
     */
    protected $negotiableQuoteFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @param CartInterfaceFactory $cartFactory
     * @param CartItemInterfaceFactory $cartItemFactory
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ExtensionAttributesFactory $extensionFactory
     * @param NegotiableQuoteItemInterfaceFactory $negotiableQuoteItemFactory
     * @param NegotiableQuoteInterfaceFactory $negotiableQuoteFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        CartInterfaceFactory $cartFactory,
        CartItemInterfaceFactory $cartItemFactory,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ExtensionAttributesFactory $extensionFactory,
        NegotiableQuoteItemInterfaceFactory $negotiableQuoteItemFactory,
        NegotiableQuoteInterfaceFactory $negotiableQuoteFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->cartFactory = $cartFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->extensionFactory = $extensionFactory;
        $this->cartItemFactory = $cartItemFactory;
        $this->negotiableQuoteItemFactory = $negotiableQuoteItemFactory;
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Removes complex values from array.
     *
     * @param array $array
     * @return array
     */
    protected function removeComplexValuesFromArray(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Converts quote object to array.
     *
     * @param CartInterface $quote
     * @return array
     */
    public function quoteToArray(CartInterface $quote)
    {
        $serialized = [];

        $quoteData = $quote->getData();
        $serialized['quote'] = $this->removeComplexValuesFromArray($quoteData);
        $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();
        $negotiableQuoteData = $negotiableQuote->getData();
        unset($negotiableQuoteData['snapshot']);
        $serialized['negotiable_quote'] = $this->removeComplexValuesFromArray($negotiableQuoteData);

        if ($quote->getShippingAddress()) {
            $shippingAddressData = $quote->getShippingAddress()->getData();
            $serialized['shipping_address'] = $this->removeComplexValuesFromArray($shippingAddressData);
        }
        if ($quote->getBillingAddress()) {
            $billingAddressData = $quote->getBillingAddress()->getData();
            $serialized['billing_address'] = $this->removeComplexValuesFromArray($billingAddressData);
        }

        $serialized['items'] = [];
        foreach ($quote->getItemsCollection() as $item) {
            $itemData = $this->removeComplexValuesFromArray($item->getData());
            if (!empty($item->getExtensionAttributes())
                && !empty($item->getExtensionAttributes()->getNegotiableQuoteItem())
            ) {
                $negotiableQuoteItem = $item->getExtensionAttributes()->getNegotiableQuoteItem();
                $itemData['negotiable_quote_item'] = $negotiableQuoteItem->getData();
            }
            $itemOptions = [];
            foreach ($item->getOptions() as $option) {
                $itemOption = $this->removeComplexValuesFromArray($option->getData());
                $productData = $option->getProduct() ? $option->getProduct()->getData() : [];
                $itemOption['product'] = $this->removeComplexValuesFromArray($productData);
                $itemOptions[] = $itemOption;
            }
            $itemData['options'] = $itemOptions;
            $serialized['items'][] = $itemData;
        }

        return $serialized;
    }

    /**
     * Detect relations between parent and child and set those relations
     * This will need, to detect in future what items to show and what to hide
     *
     * @param Collection $itemsCollection
     * @return Collection
     */
    private function resolveItemRelations(Collection $itemsCollection)
    {
        /**
         * Assign parent items
         */
        foreach ($itemsCollection->getItems() as $item) {
            if ($item->getParentItemId()) {
                $item->setParentItem($itemsCollection->getItemById($item->getParentItemId()));
            }
        }

        return $itemsCollection;
    }

    /**
     * Convert array to quote object.
     *
     * @param array $serialized
     * @return CartInterface
     */
    public function arrayToQuote(array $serialized)
    {
        $quote = $this->cartFactory->create();

        $quote->setData($serialized['quote']);

        $quote->removeAllAddresses();
        if ($serialized['shipping_address']) {
            $serializedShippingAddress = $quote->getShippingAddress();
            $serializedShippingAddress->setData($serialized['shipping_address']);
        }
        if ($serialized['billing_address']) {
            $serializedBillingAddress = $quote->getBillingAddress();
            $serializedBillingAddress->setData($serialized['billing_address']);
        }

        $quote->removeAllItems();
        $itemsCollection = $quote->getItemsCollection();
        $itemsCollection->removeAllItems();
        $notExistingProductIds = $this->getNotExistingProductIds($serialized['items']);
        $neqProductsCount = count($notExistingProductIds);

        foreach ($serialized['items'] as $key => $item) {
            if (($neqProductsCount > 0) && in_array($item['product_id'], $notExistingProductIds)) {
                unset($serialized['items'][$key]);
                continue;
            }
            $options = $item['options'];
            unset($item['options']);
            $negotiableQuoteItemData = $item['negotiable_quote_item'];
            unset($item['negotiable_quote_item']);

            $itemObject = $this->cartItemFactory->create();
            $itemObject->setData($item);
            $itemObject->setQuote($quote);
            foreach ($options as $option) {
                $productObject = $this->productFactory->create();
                $productObject->setData($option['product']);
                $option['product'] = $productObject;
                $itemObject->addOption(new \Magento\Framework\DataObject($option));
            }

            $negotiableQuoteItem = $this->negotiableQuoteItemFactory->create();
            $negotiableQuoteItem->setData($negotiableQuoteItemData);

            $itemExtensionAttributes = $this->extensionFactory->create(get_class($itemObject));
            $itemExtensionAttributes->setNegotiableQuoteItem($negotiableQuoteItem);

            $itemObject->setExtensionAttributes($itemExtensionAttributes);
            $itemsCollection->addItem($itemObject);
        }
        $this->resolveItemRelations($itemsCollection);
        $quoteExtensionAttributes = $this->extensionFactory->create(get_class($quote));
        $negotiableQuote = $this->negotiableQuoteFactory->create();
        $negotiableQuote->setData($serialized['negotiable_quote']);
        $quoteExtensionAttributes->setNegotiableQuote($negotiableQuote);
        $quote->setExtensionAttributes($quoteExtensionAttributes);
        if ($neqProductsCount > 0) {
            $quote->setTotalsCollectedFlag(false);
        }

        return $quote;
    }

    /**
     * Get array of not existing products ID's.
     *
     * @param array $quoteItems
     * @return array
     */
    private function getNotExistingProductIds(array $quoteItems)
    {
        $notExistingProducts = [];
        $filters = [];
        foreach ($quoteItems as $item) {
            $filters[] =  $this->filterBuilder
                ->setField('entity_id')
                ->setConditionType('eq')
                ->setValue($item['product_id'])
                ->create();
            $notExistingProducts[] = $item['product_id'];
        }
        $notExistingProducts = array_unique($notExistingProducts);
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->productRepository->getList($searchCriteria);
        if ($searchResults->getTotalCount() > 0) {
            foreach ($searchResults->getItems() as $item) {
                if (($key = array_search($item->getId(), $notExistingProducts)) !== false) {
                    unset($notExistingProducts[$key]);
                }
            }
        }
        return $notExistingProducts;
    }
}
