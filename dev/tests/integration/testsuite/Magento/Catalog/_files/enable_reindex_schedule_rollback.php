<?php

/* @var \Magento\Framework\Indexer\IndexerInterface $model */
$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Indexer\IndexerRegistry::class
)->get('catalogsearch_fulltext');
$model->setScheduled(false);
