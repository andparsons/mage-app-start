<?php

/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$attribute->setAttributeCode('test_attribute_code_666')
    ->setEntityTypeId(3)
    ->setIsGlobal(1)
    ->setIsUserDefined(1);
$attribute->save();
