<?php
declare(strict_types=1);

use Magento\Sales\Model\Order\Status;
use Magento\TestFramework\Helper\Bootstrap;

// phpcs:ignore Magento2.Security.IncludeFile
require 'default_rollback.php';

/** @var Status $orderStatus */
$orderStatus = Bootstrap::getObjectManager()->create(Status::class);
$orderStatus->load('custom_processing', 'status');
$orderStatus->delete();
