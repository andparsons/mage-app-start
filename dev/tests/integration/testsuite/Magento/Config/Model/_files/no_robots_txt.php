<?php
use Magento\Framework\App\Filesystem\DirectoryList;

/** @var \Magento\Framework\Filesystem\Directory\Write $rootDirectory */
$rootDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
)->getDirectoryWrite(
    DirectoryList::ROOT
);
if ($rootDirectory->isExist('robots.txt')) {
    $rootDirectory->delete('robots.txt');
}
