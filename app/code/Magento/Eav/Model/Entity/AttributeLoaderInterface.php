<?php

namespace Magento\Eav\Model\Entity;

use Magento\Framework\DataObject;

/**
 * Interface AttributeLoaderInterface
 */
interface AttributeLoaderInterface
{
    /**
     * Retrieve configuration for all attributes
     *
     * @param AbstractEntity $resource
     * @param DataObject|null $entity
     * @return AbstractEntity
     */
    public function loadAllAttributes(AbstractEntity $resource, DataObject $entity = null);
}
