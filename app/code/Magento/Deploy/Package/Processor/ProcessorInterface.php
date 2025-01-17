<?php
namespace Magento\Deploy\Package\Processor;

use Magento\Deploy\Package\Package;

/**
 * Deploy packages processor interface
 */
interface ProcessorInterface
{
    /**
     * Process package
     *
     * Package processors may produce additional (derivative) files or do additional content modifications
     *
     * @param Package $package
     * @param array $options
     * @return bool true on success
     */
    public function process(Package $package, array $options);
}
