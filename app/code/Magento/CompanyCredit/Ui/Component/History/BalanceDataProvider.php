<?php

namespace Magento\CompanyCredit\Ui\Component\History;

use Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory;
use \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider;

/**
 * Class DataProvider.
 */
class BalanceDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CustomerProvider
     */
    private $customerProvider;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param CustomerProvider $customerProvider
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        CustomerProvider $customerProvider,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->customerProvider = $customerProvider;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        $creditId = $this->getCreditIdByCompanyId();
        $this->getCollection()->addFieldToFilter('main_table.company_credit_id', ['eq' => $creditId]);
        return parent::getData();
    }

    /**
     * Get related credit id by current company id.
     *
     * @return int
     */
    private function getCreditIdByCompanyId()
    {
        if ($this->customerProvider->getCurrentUserCredit()) {
            return $this->customerProvider->getCurrentUserCredit()->getId();
        }

        return 0;
    }
}
