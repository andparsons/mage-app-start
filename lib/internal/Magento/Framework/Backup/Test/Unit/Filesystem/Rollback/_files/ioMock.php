<?php
namespace Magento\Framework\Backup\Filesystem\Rollback;

/**
 * Mock is_readable function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_readable($path)
{
    return true;
}

/**
 * Mock is_file function
 *
 * @see \Magento\Framework\Backup\Filesystem
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function is_file($path)
{
    return 2;
}
