<?php
require_once dirname(__DIR__) . '/' . 'bootstrap.php';

$objectManager->create(\Magento\Mtf\Util\Generate\Handler::class)->launch();
\Magento\Mtf\Util\Generate\GenerateResult::displayResults();
