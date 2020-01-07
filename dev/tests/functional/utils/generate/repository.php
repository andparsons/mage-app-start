<?php
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$objectManager->create(\Magento\Mtf\Util\Generate\Repository::class)->launch();
