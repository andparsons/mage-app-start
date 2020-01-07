<?php
use Magento\SalesRule\Model\Coupon;

$couponCodes = [
    'one_usage',
    'one_usage_per_customer',
];

/** @var Coupon $coupon */
$coupon = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Coupon::class);

foreach ($couponCodes as $couponCode) {
    $coupon->loadByCode($couponCode);
    $coupon->delete();
}

// phpcs:ignore Magento2.Security.IncludeFile
require 'rules_rollback.php';
