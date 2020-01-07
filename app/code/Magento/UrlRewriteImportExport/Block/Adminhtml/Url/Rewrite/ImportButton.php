<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Block\Adminhtml\Url\Rewrite;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * ImportButton in the UI
 */
class ImportButton implements ButtonProviderInterface
{
    /**
     * Get array with button configuration
     *
     * @return array
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Import'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'url_rewrite_import_form',
                                'actionName' => 'save',
                                'params' => [
                                    false
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 0,
        ];
    }
}
