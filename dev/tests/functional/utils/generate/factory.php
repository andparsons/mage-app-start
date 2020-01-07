<?php
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$magentoObjectManager->create(\Magento\Mtf\Util\Generate\Factory::class)->launch();
\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
