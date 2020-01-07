<?php
namespace Magento\Backend\Model\Search\Config\Structure;

use Magento\Config\Model\Config\StructureElementInterface;

interface ElementBuilderInterface
{
    /**
     * @param StructureElementInterface $structureElement
     * @return array
     */
    public function build(StructureElementInterface $structureElement);
}
