<?php
namespace Magento\Framework\View\Asset\PreProcessor;

/**
 * Interface FilenameResolverInterface
 */
interface FilenameResolverInterface
{
    /**
     * Resolve file name
     *
     * @param string $path
     * @return string
     */
    public function resolve($path);
}
