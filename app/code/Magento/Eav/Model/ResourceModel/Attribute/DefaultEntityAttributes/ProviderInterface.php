<?php
namespace Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes;

/**
 * Interface \Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface
 *
 */
interface ProviderInterface
{
    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     */
    public function getDefaultAttributes();
}
