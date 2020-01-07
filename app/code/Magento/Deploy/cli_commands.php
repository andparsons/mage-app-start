<?php
if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register(\Magento\Deploy\Console\CommandList::class);
}
