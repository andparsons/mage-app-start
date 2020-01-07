<?php
declare(strict_types=1);

namespace Magento\ConfigurableProductDataExporter\Model\Provider\Product;

use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Magento\CatalogDataExporter\Model\Provider\Product\OptionProviderInterface;
use Magento\ConfigurableProductDataExporter\Model\Query\ProductOptionQuery;
use Magento\ConfigurableProductDataExporter\Model\Query\ProductOptionValueQuery;
use Psr\Log\LoggerInterface;

/**
 * Class Options
 */
class Options implements OptionProviderInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductOptionQuery
     */
    private $productOptionQuery;

    /**
     * @var ProductOptionValueQuery
     */
    private $productOptionValueQuery;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Options constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ProductOptionQuery $productOptionQuery
     * @param ProductOptionValueQuery $productOptionValueQuery
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductOptionQuery $productOptionQuery,
        ProductOptionValueQuery $productOptionValueQuery,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productOptionQuery = $productOptionQuery;
        $this->productOptionValueQuery = $productOptionValueQuery;
        $this->logger = $logger;
    }

    /**
     * Get base64 encoded id for record
     *
     * @param array $row
     * @return string
     */
    private function getId(array $row) : string
    {
        return base64_encode(json_encode(['type' => 'configurable', 'id' => $row['id']]));
    }

    /**
     * Get option values
     *
     * @param array $arguments
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getOptionValues(array $arguments) : array
    {
        $connection = $this->resourceConnection->getConnection();
        $optionValues = [];
        $select = $this->productOptionValueQuery->getQuery($arguments);
        $cursor = $connection->query($select);
        while ($row = $cursor->fetch()) {
            $optionValues[$row['attribute_id']][$row['storeViewCode']][$row['optionId']] = [
                'id' => $this->getId($row),
                'value' => $row['value']
            ];
        }
        return $optionValues;
    }

    /**
     * @inheritDoc
     */
    public function get(array $values) : array
    {
        $connection = $this->resourceConnection->getConnection();
        $queryArguments = [];
        try {
            $output = [];
            $acknowledgedOption = [];
            $assignedValues = [];
            foreach ($values as $value) {
                $queryArguments['productId'][$value['productId']] = $value['productId'];
                $queryArguments['storeViewCode'][$value['storeViewCode']] = $value['storeViewCode'];
            }
            $optionValues = $this->getOptionValues($queryArguments);
            $select = $this->productOptionQuery->getQuery($queryArguments);
            $cursor = $connection->query($select);
            while ($row = $cursor->fetch()) {
                $key = $row['productId'] . $row['storeViewCode'] . $row['id'];
                $output[$key] = [
                    'productId' => $row['productId'],
                    'storeViewCode' => $row['storeViewCode'],
                    'options' => [
                        'title' => $row['title'],
                        'required' => true,
                        'multi' => false,
                        'type' => 'configurable',
                        'id' => $this->getId($row)
                    ]
                ];
                if (!isset($acknowledgedOption[$key . $row['value']])) {
                    $acknowledgedOption[$key . $row['value']] = true;
                    $assignedValues[$key]['options']['values'][] =
                        $optionValues[$row['attribute_id']][$row['storeViewCode']][$row['value']];
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData(__('Unable to retrieve product options data'));
        }
        $result = array_merge_recursive($output, $assignedValues);
        return $result;
    }
}
