<?php
namespace Magento\Setup\Module\I18n\Dictionary\Loader;

/**
 * Dictionary loader interface
 */
interface FileInterface
{
    /**
     * Load dictionary
     *
     * @param string $file
     * @return \Magento\Setup\Module\I18n\Dictionary
     * @throws \InvalidArgumentException
     */
    public function load($file);
}
