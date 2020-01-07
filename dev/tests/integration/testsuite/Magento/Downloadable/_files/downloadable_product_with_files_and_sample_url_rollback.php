<?php

use Magento\Downloadable\Api\DomainManagerInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var DomainManagerInterface $domainManager */
$domainManager = $objectManager->get(DomainManagerInterface::class);
$domainManager->removeDomains(['sampleurl.com']);

// @codingStandardsIgnoreLine
require __DIR__ . '/product_downloadable_rollback.php';
