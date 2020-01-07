<?php

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model->load('address_user_attribute', 'attribute_code')->delete();
