<?php
namespace Magento\AdvancedRule\Model\Condition;

/**
 * Interface \Magento\AdvancedRule\Model\Condition\FilterTextGeneratorInterface
 *
 */
interface FilterTextGeneratorInterface
{
    /**
     * @param \Magento\Framework\DataObject $input
     * @return string[]
     */
    public function generateFilterText(\Magento\Framework\DataObject $input);
}
