<?php

use \Magento\Framework\App\ObjectManager;
use \Magento\Framework\FlagManager;
use \Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;

/**
 * @var FlagManager $flagManager
 */
$flagManager = ObjectManager::getInstance()->get(FlagManager::class);
$flagManager->saveFlag(
    WebsiteAttributesSynchronizer::FLAG_NAME,
    WebsiteAttributesSynchronizer::FLAG_SYNCHRONIZED
);
