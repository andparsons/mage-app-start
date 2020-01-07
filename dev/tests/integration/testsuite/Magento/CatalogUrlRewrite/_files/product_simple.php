<?php

\Magento\TestFramework\Helper\Bootstrap::getInstance()
    ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

require __DIR__ . '/../../Catalog/_files/product_simple.php';
