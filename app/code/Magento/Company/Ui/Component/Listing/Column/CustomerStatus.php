<?php
namespace Magento\Company\Ui\Component\Listing\Column;

use Magento\Company\Api\Data\CompanyCustomerInterface;

class CustomerStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $item[$fieldName] = $this->setStatusLabel($item[$fieldName]);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Set status label.
     *
     * @param int $key
     * @return string
     */
    protected function setStatusLabel($key)
    {
        $labels = [
            CompanyCustomerInterface::STATUS_ACTIVE => __('Active'),
            CompanyCustomerInterface::STATUS_INACTIVE => __('Inactive'),
        ];

        return $labels[$key];
    }
}
