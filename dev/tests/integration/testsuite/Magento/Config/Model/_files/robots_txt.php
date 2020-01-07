<?php
use Magento\Framework\App\Filesystem\DirectoryList;

/** @var \Magento\Framework\Filesystem\Directory\Write $rootDirectory */
$rootDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
)->getDirectoryWrite(
    DirectoryList::ROOT
);
$rootDirectory->copyFile($rootDirectory->getRelativePath(__DIR__ . '/robots.txt'), 'robots.txt');
