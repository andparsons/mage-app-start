<?php

if (PHP_SAPI == 'cli') {
    \Magento\Framework\Console\CommandLocator::register(\Magento\SampleData\Console\CommandList::class);
}
