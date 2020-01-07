<?php
namespace Magento\Framework\View\Asset;

use Magento\Framework\View\Asset\File;

/**
 * Interface ContentProcessorInterface
 */
interface ContentProcessorInterface
{
    /**
     * Error prefix
     */
    const ERROR_MESSAGE_PREFIX = 'Compilation from source: ';

    /**
     * Process file content
     *
     * @param File $asset
     * @return string
     */
    public function processContent(File $asset);
}
