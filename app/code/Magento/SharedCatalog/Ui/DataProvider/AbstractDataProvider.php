<?php
namespace Magento\SharedCatalog\Ui\DataProvider;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class AbstractDataProvider
 */
abstract class AbstractDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\App\RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigData()
    {
        return $this->prepareConfig(parent::getConfigData());
    }

    /**
     * Prepare configuration
     *
     * @param array $configData
     * @return array
     */
    protected function prepareConfig(array $configData)
    {
        return $configData;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = $this->prepareCollection();
        }
        return $this->collection;
    }

    /**
     * Prepare collection
     *
     * @return AbstractCollection
     */
    abstract protected function prepareCollection();

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $this->getCollection()->getSize();

        $arrItems['items'] = [];
        foreach ($this->getCollection()->getItems() as $item) {
            $this->prepareDataItem($item);
            $arrItems['items'][] =  $item->toArray();
        }

        return $arrItems;
    }

    /**
     * Prepare data item
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function prepareDataItem(\Magento\Framework\DataObject $item)
    {
        return $item;
    }

    /**
     * Prepares url
     *
     * @param array $configData
     * @param string $urlConfigKey
     * @return array
     */
    protected function prepareUrl(array $configData, $urlConfigKey)
    {
        if (!isset($configData['filter_url_params'])) {
            return $configData;
        }
        foreach ($configData['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }

            if ($paramValue) {
                $configData[$urlConfigKey] = sprintf(
                    '%s%s/%s/',
                    $configData[$urlConfigKey],
                    $paramName,
                    $paramValue
                );
            }
        }

        return $configData;
    }
}
