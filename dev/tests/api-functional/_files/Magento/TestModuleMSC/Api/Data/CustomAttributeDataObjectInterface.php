<?php
namespace Magento\TestModuleMSC\Api\Data;

interface CustomAttributeDataObjectInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const NAME = 'name';

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
