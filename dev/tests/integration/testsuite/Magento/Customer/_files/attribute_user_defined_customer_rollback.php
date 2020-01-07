<?php

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model->load('user_attribute', 'attribute_code')->delete();
