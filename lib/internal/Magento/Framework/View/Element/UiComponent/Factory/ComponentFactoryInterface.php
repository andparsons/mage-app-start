<?php
namespace Magento\Framework\View\Element\UiComponent\Factory;

/**
 * Interface \Magento\Framework\View\Element\UiComponent\Factory\ComponentFactoryInterface
 *
 */
interface ComponentFactoryInterface
{
    /**
     * Create child components
     *
     * @param array $bundleComponents
     * @param array $arguments
     * @return bool|mixed
     */
    public function create(array &$bundleComponents, array $arguments = []);
}
