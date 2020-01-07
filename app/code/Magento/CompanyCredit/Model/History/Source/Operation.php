<?php
namespace Magento\CompanyCredit\Model\History\Source;

use Magento\CompanyCredit\Model\HistoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Operation.
 */
class Operation extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**
     * Retrieve option array.
     *
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            HistoryInterface::TYPE_ALLOCATED => __('Allocated'),
            HistoryInterface::TYPE_UPDATED => __('Updated'),
            HistoryInterface::TYPE_PURCHASED => __('Purchased'),
            HistoryInterface::TYPE_REIMBURSED => __('Reimbursed'),
            HistoryInterface::TYPE_REFUNDED => __('Refunded'),
            HistoryInterface::TYPE_REVERTED => __('Reverted'),
        ];
    }

    /**
     * Get all options.
     *
     * @return array
     */
    public function getAllOptions()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
