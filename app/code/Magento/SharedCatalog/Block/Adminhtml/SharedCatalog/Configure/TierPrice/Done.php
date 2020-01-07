<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Configure\TierPrice;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Done button logic.
 */
class Done implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Done'),
            'class' => 'save primary',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'shared_catalog_tier_price_form.shared_catalog_tier_price_form',
                                'actionName' => 'save'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
