<?php
namespace Magento\AdvancedSalesRule\Model\Rule\Condition;

/**
 * Class Filter
 * @package Magento\AdvancedSalesRule\Model\Rule\Condition
 *
 * @method int getRuleId()
 * @method setRuleId($ruleId)
 * @method int getGroupId()
 * @method setGroupId($groupId)
 *
 * @codeCoverageIgnore
 */
class Filter extends \Magento\AdvancedRule\Model\Condition\Filter
{
    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Magento\AdvancedSalesRule\Model\ResourceModel\Rule\Condition\Filter::class);
    }
}
