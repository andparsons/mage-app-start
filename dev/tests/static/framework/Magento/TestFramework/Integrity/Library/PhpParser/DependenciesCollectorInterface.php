<?php
namespace Magento\TestFramework\Integrity\Library\PhpParser;

/**
 * Collect dependencies
 *
 */
interface DependenciesCollectorInterface
{
    /**
     * Return list of dependencies
     *
     * @param Uses $uses
     * @return string[]
     */
    public function getDependencies(Uses $uses);
}
