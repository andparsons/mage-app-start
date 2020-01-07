<?php

include 'simple_product.php';
/** @var $product \Magento\Catalog\Model\Product */
$product->setStockData(['use_config_manage_stock' => 0, 'min_sale_qty' => 3]);
$product->save();
