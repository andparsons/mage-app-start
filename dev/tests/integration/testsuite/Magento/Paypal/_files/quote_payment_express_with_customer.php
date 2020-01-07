<?php
require __DIR__ . '/quote_express_with_customer.php';

/** @var $service \Magento\Quote\Api\CartManagementInterface */
$service = $objectManager->create(\Magento\Quote\Api\CartManagementInterface::class);
$order = $service->submit($quote, ['increment_id' => '100000002']);
