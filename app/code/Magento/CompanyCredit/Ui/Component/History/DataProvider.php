<?php

namespace Magento\CompanyCredit\Ui\Component\History;

use Magento\CompanyCredit\Model\ResourceModel\History\CollectionFactory;
use Magento\CompanyCredit\Api\CreditDataProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\CompanyCredit\Model\HistoryFactory;

/**
 * Class DataProvider.
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @var \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider
     */
    private $customerProvider;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param CreditDataProviderInterface $creditDataProvider
     * @param \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider $customerProvider
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        CreditDataProviderInterface $creditDataProvider,
        \Magento\CompanyCredit\Model\CreditDetails\CustomerProvider $customerProvider,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->collection = $collectionFactory->create();
        $this->creditDataProvider = $creditDataProvider;
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
     * Get related History by current company.
     *
     * @return array
     */
    public function getCreditIdByCompanyId()
    {
        if ($this->customerProvider->getCurrentUserCredit()) {
            return $this->customerProvider->getCurrentUserCredit()->getId();
        }

        if (!$this->request->getParam('id')) {
            return 0;
        }

        $credit = $this->creditDataProvider->get($this->request->getParam('id'));

        return (int)$credit->getId();
    }
}
