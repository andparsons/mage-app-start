<?php
namespace Magento\Config\Model\Config;

/**
 * @api
 * @since 101.1.0
 */
interface StructureElementInterface extends Structure\ElementInterface
{
    /**
     * Retrieve element config path
     *
     * @param string $fieldPrefix
     * @return string
     * @since 101.1.0
     */
    public function getPath($fieldPrefix = '');
}
