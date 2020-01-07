<?php
namespace Magento\Company\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Customer mysql resource.
 */
class Customer extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('company_advanced_customer_entity', 'customer_id');
    }

    /**
     * Get Customers by company ID.
     *
     * @param int $companyId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerIdsByCompanyId($companyId)
    {
        $connection = $this->getConnection();
        $data = $connection->fetchAll(
            $connection->select()->from(
                ['ac' => $this->getMainTable()],
                ['ac.customer_id']
            )->where(
                'ac.company_id = ?',
                $companyId
            )
        );

        return array_map(
            function ($row) {
                return $row['customer_id'];
            },
            $data
        );
    }

    /**
     * Get Customer extension attributes.
     *
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerExtensionAttributes($customerId)
    {
        $connection = $this->getConnection();
        if ($data = $connection->fetchRow(
            $connection->select()->from(
                ['ac' => $this->getMainTable()]
            )->where(
                'ac.customer_id = ?',
                $customerId
            )->limit(
                1
            )
        )
        ) {
            return $data;
        }
        return [];
    }

    /**
     * Save custom attributes.
     *
     * @param CompanyCustomerInterface $customerExtension
     * @return $this
     * @throws CouldNotSaveException
     */
    public function saveAdvancedCustomAttributes(
        CompanyCustomerInterface $customerExtension
    ) {
        $customerExtensionData = $this->_prepareDataForSave($customerExtension);
        if ($customerExtensionData) {
            try {
                $this->getConnection()->insertOnDuplicate(
                    $this->getMainTable(),
                    $customerExtensionData,
                    array_keys($customerExtensionData)
                );
            } catch (\Exception $e) {
                throw new CouldNotSaveException(__('There was an error saving custom attributes.'));
            }
        }
        return $this;
    }
}
