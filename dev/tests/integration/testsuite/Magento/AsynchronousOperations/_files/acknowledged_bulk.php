<?php
require __DIR__ . '/bulk.php';

$acknowledgedBulkTable = $resource->getTableName('magento_acknowledged_bulk');
$acknowledgedBulkQuery = "INSERT INTO {$acknowledgedBulkTable} (`bulk_uuid`) VALUES ('bulk-uuid-4'), ('bulk-uuid-5');";
$connection->query($acknowledgedBulkQuery);
