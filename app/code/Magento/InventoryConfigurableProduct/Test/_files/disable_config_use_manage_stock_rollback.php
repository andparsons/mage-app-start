<?php
declare(strict_types=1);

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\Config\Value;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var Value $value */
$value = $objectManager->get(Value::class);
$value->setPath(Configuration::XML_PATH_MANAGE_STOCK);
$value->setValue('1');
$value->save();
