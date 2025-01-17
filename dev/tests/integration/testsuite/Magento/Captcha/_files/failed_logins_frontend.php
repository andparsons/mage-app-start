<?php
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Captcha\Model\ResourceModel\LogFactory;
use Magento\Captcha\Model\ResourceModel\Log;

$objectManager = Bootstrap::getObjectManager();
$logFactory = $objectManager->get(LogFactory::class);

/** @var Log $captchaLog */
$captchaLog = $logFactory->create();
$captchaLog->logAttempt('mageuser@dummy.com');
