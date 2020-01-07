<?php

/** @var \Magento\Framework\App\ResourceConnection $resource */
$resource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Framework\App\ResourceConnection::class);

$connection = $resource->getConnection('default');
$connection->truncateTable($resource->getTableName('search_synonyms'));
