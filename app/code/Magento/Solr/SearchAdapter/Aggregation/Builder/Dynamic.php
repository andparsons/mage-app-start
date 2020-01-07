<?php
namespace Magento\Solr\SearchAdapter\Aggregation\Builder;

use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\Algorithm\Repository;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Dynamic\EntityStorageFactory;
use Magento\Framework\Search\Request\Aggregation\DynamicBucket;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Solarium\Core\Query\Result\ResultInterface;

class Dynamic implements BucketBuilderInterface
{
    /**
     * @var Repository
     */
    private $algorithmRepository;

    /**
     * @var EntityStorageFactory
     */
    private $entityStorageFactory;

    /**
     * @param Repository $algorithmRepository
     * @param EntityStorageFactory $entityStorageFactory
     */
    public function __construct(Repository $algorithmRepository, EntityStorageFactory $entityStorageFactory)
    {
        $this->algorithmRepository = $algorithmRepository;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        ResultInterface $baseQueryResult,
        DataProviderInterface $dataProvider
    ) {
        /** @var DynamicBucket $bucket */
        $algorithm = $this->algorithmRepository->get($bucket->getMethod(), ['dataProvider' => $dataProvider]);
        $data = $algorithm->getItems($bucket, $dimensions, $this->getEntityStorage($baseQueryResult));
        $resultData = $this->prepareData($data);

        return $resultData;
    }

    /**
     * Extract Document ids
     *
     * @param ResultInterface $baseQueryResult
     * @return EntityStorage
     */
    private function getEntityStorage(ResultInterface $baseQueryResult)
    {
        $ids = [];
        foreach ($baseQueryResult as $document) {
            $ids[] = $document->id;
        }

        return $this->entityStorageFactory->create($ids);
    }

    /**
     * Prepare result data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        $resultData = [];
        foreach ($data as $value) {
            $from = is_numeric($value['from']) ? $value['from'] : '*';
            $to = is_numeric($value['to']) ? $value['to'] : '*';
            unset($value['from'], $value['to']);

            $rangeName = "{$from}_{$to}";
            $resultData[$rangeName] = array_merge(['value' => $rangeName], $value);
        }

        return $resultData;
    }
}
