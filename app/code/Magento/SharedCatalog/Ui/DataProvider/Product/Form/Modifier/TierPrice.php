<?php
namespace Magento\SharedCatalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Tier price modifier.
 */
class TierPrice extends AbstractModifier
{
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if (isset($meta['advanced_pricing_modal']['children']['advanced-pricing']['children']['tier_price'])) {
            $meta['advanced_pricing_modal']['children']['advanced-pricing']['children']['tier_price']
            ['children']['cust_group']['arguments']['data']['config']['component'] =
                'Magento_B2b/js/form/element/ui-group';
            $meta['advanced_pricing_modal']['children']['advanced-pricing']['children']['tier_price']
            ['children']['cust_group']['arguments']['data']['config']['elementTmpl'] =
                'Magento_B2b/form/element/ui-group';
            $meta['advanced_pricing_modal']['children']['advanced-pricing']['children']['tier_price']
            ['arguments']['data']['config']['label'] = __('Catalog and Tier Price');
            $meta['advanced_pricing_modal']['children']['advanced-pricing']['children']['tier_price']
            ['children']['cust_group']['arguments']['data']['config']['label'] =
                __('Group or Catalog');
        }

        return $meta;
    }
}
