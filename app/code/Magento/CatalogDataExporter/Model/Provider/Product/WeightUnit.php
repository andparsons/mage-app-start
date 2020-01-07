<?php
declare(strict_types=1);

namespace Magento\CatalogDataExporter\Model\Provider\Product;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\DataExporter\Exception\UnableRetrieveData;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Class WeightType
 */
class WeightUnit
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get weight unit values for product/store view code
     *
     * @param array $values
     * @return array
     * @throws UnableRetrieveData
     */
    public function get(array $values) : array
    {
        $queryArguments = [];
        try {
            $output = [];
            foreach ($values as $value) {
                $queryArguments['productId'][$value['productId']] = $value['productId'];
                $queryArguments['storeViewCode'][$value['storeViewCode']] = $value['storeViewCode'];
            }
            $weightUnits = $this->getWeightUnit($queryArguments['storeViewCode']);

            foreach ($values as $row) {
                if (!is_null($row['weight'])) {
                    $output[] = [
                        'productId' => $row['productId'],
                        'storeViewCode' => $row['storeViewCode'],
                        'weightUnit' => $weightUnits[$row['storeViewCode']],
                    ];
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            throw new UnableRetrieveData(__('Unable to retrieve weight type data'));
        }
        return $output;
    }

    /**
     * Get weight units for all store view codes
     *
     * @param array $storeViewCodes
     * @return array
     */
    private function getWeightUnit(array $storeViewCodes) : array
    {
        $weightTypes = [];
        foreach ($storeViewCodes as $storeViewCode) {
            $weightTypes[$storeViewCode] = $this->scopeConfig->getValue(
                Data::XML_PATH_WEIGHT_UNIT,
                ScopeInterface::SCOPE_STORE,
                $storeViewCode
            );
        }
        return $weightTypes;
    }
}
