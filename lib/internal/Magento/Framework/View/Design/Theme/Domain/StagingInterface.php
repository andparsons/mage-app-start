<?php
namespace Magento\Framework\View\Design\Theme\Domain;

/**
 * Interface StagingInterface
 */
interface StagingInterface
{
    /**
     * Copy changes from 'staging' theme
     *
     * @return \Magento\Framework\View\Design\Theme\Domain\StagingInterface
     */
    public function updateFromStagingTheme();
}
