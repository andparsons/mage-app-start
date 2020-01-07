<?php
namespace Magento\Company\Model\Company\Source;

use Magento\Company\Model\Company;

/**
 * Class Status
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => __('Active'), 'value' => Company::STATUS_APPROVED],
            ['label' => __('Pending Approval'), 'value' => Company::STATUS_PENDING],
            ['label' => __('Rejected'), 'value' => Company::STATUS_REJECTED],
            ['label' => __('Blocked'), 'value' => Company::STATUS_BLOCKED]
        ];
        return $options;
    }
}
