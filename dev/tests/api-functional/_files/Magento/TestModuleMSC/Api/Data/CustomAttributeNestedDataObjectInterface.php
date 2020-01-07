<?php
namespace Magento\TestModuleMSC\Api\Data;

interface CustomAttributeNestedDataObjectInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);
}
