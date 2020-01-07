<?php
declare(strict_types=1);

namespace Magento\SaaSCatalog\Cron;

use Magento\CatalogDataExporter\Model\Feed\Products as ProductsFeed;
use Magento\DataExporter\Exception\UnableSendData;
use Magento\DataExporter\Http\Command\SubmitFeed;
use Magento\Framework\FlagManager;
use Magento\Framework\Module\ModuleList;
use Magento\SaaSCatalog\Model\ProductFeedRegistry;
use Psr\Log\LoggerInterface;

/**
 * Class SubmitProductFeed
 */
class SubmitProductFeed
{
    /**
     * @var SubmitFeed
     */
    private $submitFeed;

    /**
     * @var ModuleList
     */
    private $moduleList;
    /**
     * @var ProductsFeed
     */
    private $productsFeed;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @var ProductFeedRegistry
     */
    private $feedRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private static $chunkSize = 100;

    /**
     * @var int
     */
    private static $iterations = 5;

    /**
     * SubmitProductFeed constructor.
     *
     * @param ProductsFeed $productsFeed
     * @param SubmitFeed $submitFeed
     * @param ModuleList $moduleList
     * @param FlagManager $flagManager
     * @param ProductFeedRegistry $feedRegistry
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductsFeed $productsFeed,
        SubmitFeed $submitFeed,
        ModuleList $moduleList,
        FlagManager $flagManager,
        ProductFeedRegistry $feedRegistry,
        LoggerInterface $logger
    ) {
        $this->productsFeed = $productsFeed;
        $this->submitFeed = $submitFeed;
        $this->moduleList = $moduleList;
        $this->flagManager = $flagManager;
        $this->feedRegistry = $feedRegistry;
        $this->logger = $logger;
    }

    /**
     * Submit feed data
     *
     * @param array $data
     * @return bool
     * @throws UnableSendData
     */
    public function submitFeed(array $data) : bool
    {
        $chunks = array_chunk($data['feed'], self::$chunkSize);
        $result = true;
        foreach ($chunks as $chunk) {
            $filteredData = $this->feedRegistry->filter($chunk);
            if (!empty($filteredData)) {
                $result = $this->submitFeed->execute(
                    'products',
                    $filteredData,
                    [
                        sprintf(
                            'module-version:%s',
                            $this->moduleList->getOne('Magento_CatalogDataExporter')['setup_version']
                        )
                    ]
                );
                if (!$result) {
                    return $result;
                } else {
                    $this->feedRegistry->registerFeed($filteredData);
                }
            }
        }
        return $result;
    }

    /**
     * Iteration of data submission
     *
     * @throws \Zend_Db_Statement_Exception
     */
    private function iteration()
    {
        $lastSyncTimestamp = $this->flagManager->getFlagData('products-feed-version');
        $data = $this->productsFeed->getFeedSince($lastSyncTimestamp ? $lastSyncTimestamp : '1');
        try {
            if ($data['recentTimestamp'] !== null) {
                $result = $this->submitFeed($data);
                if ($result) {
                    $this->flagManager->saveFlag('products-feed-version', $data['recentTimestamp']);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Site verification and claiming
     *
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function execute()
    {
        for ($i=0; $i < self::$iterations; $i++) {
            $this->iteration();
        }
    }
}
