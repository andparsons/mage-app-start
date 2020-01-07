<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Ui\Component\Import;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\UrlRewriteImportExport\Model\Import;

/**
 * The list of behaviors
 */
class Behavior implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            Import::BEHAVIOR_ADD_UPDATE => [
                'label' => __('Add/Update'),
                'value' => Import::BEHAVIOR_ADD_UPDATE,
            ],
            Import::BEHAVIOR_DELETE => [
                'label' => __('Delete'),
                'value' => Import::BEHAVIOR_DELETE,
            ],
        ];
    }
}
