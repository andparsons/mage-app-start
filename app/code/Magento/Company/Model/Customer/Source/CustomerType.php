<?php
namespace Magento\Company\Model\Customer\Source;

use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Option Source for customer type.
 */
class CustomerType implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getOptions() as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value
            ];
        }

        return $options;
    }

    /**
     * Get option label by value.
     *
     * @param int $value
     * @return string|null
     */
    public function getLabel($value)
    {
        $options = $this->getOptions();

        return $options[$value] ?? null;
    }

    /**
     * Get customer type options.
     *
     * @return array
     */
    private function getOptions()
    {
        return [
            CompanyCustomerInterface::TYPE_COMPANY_ADMIN => __('Company admin'),
            CompanyCustomerInterface::TYPE_COMPANY_USER => __('Company user'),
            CompanyCustomerInterface::TYPE_INDIVIDUAL_USER => __('Individual user'),
        ];
    }
}
