<?php
declare(strict_types=1);

namespace Magento\DataServices\Model;

/**
 * Model for getting extension versions from filesystem
 */
interface VersionFinderInterface
{
    /**
     * Get extension version from root composer
     *
     * @param string $packageName
     * @return string|null
     */
    public function getVersionFromComposer(string $packageName);

    /**
     * Get extension version from composer files
     *
     * @param string $moduleName
     * @param string $packageName
     * @return string|null
     */
    public function getVersionFromFiles(string $moduleName, string $packageName);
}
