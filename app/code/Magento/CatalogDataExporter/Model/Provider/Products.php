<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider;

use Magento\CatalogDataExporter\Model\Provider\Product\Formatter\FormatterInterface;
use Magento\CatalogDataExporter\Model\Query\MainProductQuery;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Products data provider
 */
class Products
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MainProductQuery
     */
    private $mainProductQuery;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Products constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param MainProductQuery $mainProductQuery
     * @param FormatterInterface $formatter
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        MainProductQuery $mainProductQuery,
        FormatterInterface $formatter,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mainProductQuery = $mainProductQuery;
        $this->formatter = $formatter;
        $this->logger = $logger;
    }

    /**
     * Format provider data
     *
     * @param array $row
     * @return array
     */
    private function format(array $row) : array
    {
        $output = $row;
        $output = $this->formatter->format($output);
        return $output;
    }

    /**
     * Get provider data
     *
     * @param array $values
     * @return array
     * @throws UnableRetrieveData
     */
    public function get(array $values) : array
    {
        $output = [];
        $queryArguments = [];
        try {
            foreach ($values as $value) {
                $queryArguments['productId'][$value['productId']] = $value['productId'];
            }
            $connection = $this->resourceConnection->getConnection();
            $select = $this->mainProductQuery->getQuery($queryArguments);
            $cursor = $connection->query($select);
            while ($row = $cursor->fetch()) {
                $output[] = $this->format($row);
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData(__('Unable to retrieve product data'));
        }
        return $output;
    }
}
