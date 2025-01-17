<?php
declare(strict_types=1);

use Magento\Quote\Api\CartRepositoryInterface;

$store = $storeManager->getStore();
$quote->setReservedOrderId('multishipping_quote_id_braintree')
    ->setStoreId($store->getId())
    ->setCustomerEmail('customer001@test.com');

/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quote->collectTotals();
$quoteRepository->save($quote);

$items = $quote->getAllItems();
$addressList = $quote->getAllShippingAddresses();

foreach ($addressList as $key => $address) {
    $item = $items[$key];
    // set correct quantity per shipping address
    $item->setQty(1);
    $address->setTotalQty(1);
    $address->addItem($item);
}

// assign virtual product to the billing address
$billingAddress = $quote->getBillingAddress();
$virtualItem = $items[sizeof($items) - 1];
$billingAddress->setTotalQty(1);
$billingAddress->addItem($virtualItem);

// need to recollect totals
$quote->setTotalsCollectedFlag(false);
$quote->collectTotals();
$quoteRepository->save($quote);
