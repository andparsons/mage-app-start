<?php
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$objectManager->create(\Magento\Mtf\Util\Generate\Page::class)->launch();
\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
