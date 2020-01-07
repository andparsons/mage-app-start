<?php

/** @var $cache \Magento\Framework\App\Cache */
$cache = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Framework\App\Cache::class);
$cache->clean([\Magento\Theme\Model\Design::CACHE_TAG]);
