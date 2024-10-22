<?php
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface VirtualInterface
 */
interface VirtualInterface
{
    /**
     * Get 'staging' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getStagingTheme();

    /**
     * Get 'physical' theme
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function getPhysicalTheme();
}
