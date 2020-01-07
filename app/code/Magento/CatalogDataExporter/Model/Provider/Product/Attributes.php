<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product;

use Magento\CatalogDataExporter\Model\Query\ProductAttributeQuery;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Class Attributes
 */
class Attributes
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductAttributeQuery
     */
    private $attributeQuery;

    /**
     * @var AttributeMetadata
     */
    private $attributeMetadata;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Attributes constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param ProductAttributeQuery $attributeQuery
     * @param AttributeMetadata $attributeMetadata
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductAttributeQuery $attributeQuery,
        AttributeMetadata $attributeMetadata,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->attributeQuery = $attributeQuery;
        $this->attributeMetadata = $attributeMetadata;
        $this->logger = $logger;
    }

    /**
     * Format attribute
     *
     * @param string $attributeCode
     * @param string $storeViewCode
     * @param null|string $value
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function formatAttribute(string $attributeCode, string $storeViewCode, ?string $value) : array
    {
        $output = [];
        $options = $this->attributeMetadata->getOptions($attributeCode, $storeViewCode);

        if ($options != null) {
            $optionIds = explode(',', $value);
            foreach ($optionIds as $optionId) {
                if (isset($options[$optionId])) {
                    $output[] = $options[$optionId];
                } else {
                    $output[] = $optionId;
                }
            }
        } else {
            $output[] = $value;
        }
        return $output;
    }

    /**
     * Get provider data
     *
     * @param array $values
     * @return array
     * @throws UnableRetrieveData
     * @throws \Zend_Db_Statement_Exception
     */
    public function get(array $values): array
    {
        $output = [];
        $connection = $this->resourceConnection->getConnection();
        $queryArguments = [];
        foreach ($values as $value) {
            $queryArguments['productId'][$value['productId']] = $value['productId'];
            $queryArguments['storeViewCode'][$value['storeViewCode']] = $value['storeViewCode'];
        }
        try {
            foreach ($queryArguments['storeViewCode'] as $storeViewCode) {
                $select = $this->attributeQuery->getQuery(
                    [
                        'productId' => $queryArguments['productId'],
                        'storeViewCode' => $storeViewCode
                    ]
                );
                $cursor = $connection->query($select);
                while ($row = $cursor->fetch()) {
                    $key = implode('-', [$storeViewCode, $row['productId'], $row['attributeCode']]);
                    $output[$key]['productId'] = $row['productId'];
                    $output[$key]['storeViewCode'] = $storeViewCode;
                    $output[$key]['attributes'] = [
                        'attributeCode' => $row['attributeCode'],
                        'value' => ($row['value'] != null) ?
                                $this->formatAttribute($row['attributeCode'], $storeViewCode, $row['value'])
                                : null
                    ];
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData(__('Unable to retrieve attributes data'));
        }
        return $output;
    }
}
