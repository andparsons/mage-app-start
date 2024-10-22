<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $agreement \Magento\CheckoutAgreements\Model\Agreement */
$agreement = $objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
$agreement->load('Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreement->delete();
}
