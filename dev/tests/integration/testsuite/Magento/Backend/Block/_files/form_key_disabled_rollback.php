<?php

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Backend\Model\UrlInterface::class
)->turnOnSecretKey();
