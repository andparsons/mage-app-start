<?php
namespace Magento\Setup\Model;

/**
 * Generate description for product
 */
interface DescriptionGeneratorInterface
{
    /**
     * Generate description per product net
     *
     * @param int $entityIndex
     * @return string
     */
    public function generate($entityIndex);
}
