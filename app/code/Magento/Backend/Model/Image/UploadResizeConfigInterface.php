<?php
declare(strict_types=1);

namespace Magento\Backend\Model\Image;

/**
 * Interface UploadResizeConfigInterface
 *
 * Used to retrieve configuration for frontend image uploader
 */
interface UploadResizeConfigInterface
{
    /**
     * Get maximal width value for resized image
     *
     * @return int
     */
    public function getMaxWidth(): int;

    /**
     * Get maximal height value for resized image
     *
     * @return int
     */
    public function getMaxHeight(): int;

    /**
     * Get config value for frontend resize
     *
     * @return bool
     */
    public function isResizeEnabled(): bool;
}
