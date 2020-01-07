<?php
namespace Magento\Ui\DataProvider\Modifier;

/**
 * Interface \Magento\Ui\DataProvider\Modifier\PoolInterface
 *
 */
interface PoolInterface
{
    /**
     * Retrieve modifiers
     *
     * @return array
     */
    public function getModifiers();

    /**
     * Retrieve modifiers instantiated
     *
     * @return ModifierInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getModifiersInstances();
}
