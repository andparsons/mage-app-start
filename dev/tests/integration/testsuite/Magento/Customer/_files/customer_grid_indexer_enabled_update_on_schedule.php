<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(\Magento\Framework\Indexer\IndexerRegistry::class);
$indexer = $indexerRegistry->get(\Magento\Customer\Model\Customer::CUSTOMER_GRID_INDEXER_ID);
$indexer->setScheduled(true);
