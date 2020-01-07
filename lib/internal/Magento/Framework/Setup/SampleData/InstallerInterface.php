<?php
namespace Magento\Framework\Setup\SampleData;

/**
 * Interface for SampleData modules installation
 */
interface InstallerInterface
{
    /**
     * Install SampleData module
     *
     * @return void
     */
    public function install();
}
