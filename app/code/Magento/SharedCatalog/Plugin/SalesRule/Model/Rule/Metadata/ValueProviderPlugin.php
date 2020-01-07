<?php
namespace Magento\SharedCatalog\Plugin\SalesRule\Model\Rule\Metadata;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider;

/**
 * Class ValueProviderPlugin.
 */
class ValueProviderPlugin
{
    /**
     * Deletes all options from customer group field.
     *
     * @param ValueProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMetadataValues(ValueProvider $subject, array $result)
    {
        if (!is_array($result)) {
            return $result;
        }
        if (isset($result['rule_information']['children']
            ['customer_group_ids']['arguments']['data']['config']['options'])) {
            $result['rule_information']['children']
            ['customer_group_ids']['arguments']['data']['config']['options'] = [];
        }
        return $result;
    }
}
