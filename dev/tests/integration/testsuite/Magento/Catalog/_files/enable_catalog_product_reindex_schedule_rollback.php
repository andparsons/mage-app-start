<?php
declare(strict_types=1);

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\TestFramework\Helper\Bootstrap;

$indexerProcessor = Bootstrap::getObjectManager()->get(Processor::class);
$indexerProcessor->getIndexer()->setScheduled(false);
