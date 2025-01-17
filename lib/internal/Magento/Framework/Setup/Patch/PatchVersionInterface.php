<?php
namespace Magento\Framework\Setup\Patch;

/**
 * For backward compatibility with versioned style module installation. Deprecated since creation.
 *
 * @deprecated 102.0.0
 */
interface PatchVersionInterface
{
    /**
     * This version associate patch with Magento setup version.
     * For example, if Magento current setup version is 2.0.3 and patch version is 2.0.2 then
     * this patch will be added to registry, but will not be applied, because it is already applied
     * by old mechanism of UpgradeData.php script
     *
     * @return string
     * @deprecated 102.0.0 since appearance, required for backward compatibility
     */
    public static function getVersion();
}
