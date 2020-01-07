<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Configure\TierPrice;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Save
 */
class Cancel implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label' => __('Cancel'),
            'class' => 'cancel',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'index = tier_price_modal',
                                'actionName' => 'closeModal'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
