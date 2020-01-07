<?php
$block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Cms\Model\Page::class);
$block->load('no-route', 'identifier');
$block->setIsActive(0)->save();
