<?php
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$objectManager->create(\Magento\Mtf\Util\Generate\Fixture::class)->launch();
