<?php

// refresh report statistics
/** @var \Magento\Sales\Model\ResourceModel\Report\Shipping $reportResource */
$reportResource = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\ResourceModel\Report\Shipping::class
);
$reportResource->beginTransaction();
// prevent table truncation by incrementing the transaction nesting level counter
try {
    $reportResource->aggregate();
    $reportResource->commit();
} catch (\Exception $e) {
    $reportResource->rollBack();
    throw $e;
}
