<?php

include __DIR__ . '/quote.php';
include __DIR__ . '/../../../Magento/Customer/_files/customer.php';

/** @var $quote \Magento\Quote\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->load('test01', 'reserved_order_id');
/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customer */
$customerRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customerId = 1;
$customer = $customerRepository->getById($customerId);
$quote->setCustomer($customer)->setCustomerIsGuest(false)->save();
foreach ($quote->getAllAddresses() as $address) {
    $address->setCustomerId($customerId)->save();
}

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
