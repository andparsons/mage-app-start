<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->load('test_order_1_not_default_store_guest', 'reserved_order_id')->delete();
