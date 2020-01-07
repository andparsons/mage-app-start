<?php
namespace Magento\Setup\Module\Di\Code\Scanner;

/**
 * Interface \Magento\Setup\Module\Di\Code\Scanner\ScannerInterface
 *
 */
interface ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files);
}
