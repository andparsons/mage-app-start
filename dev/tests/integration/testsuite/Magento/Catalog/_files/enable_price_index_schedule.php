<?php
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\TestFramework\Helper\Bootstrap;

$indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
$indexerProcessor->getIndexer()->setScheduled(true);
