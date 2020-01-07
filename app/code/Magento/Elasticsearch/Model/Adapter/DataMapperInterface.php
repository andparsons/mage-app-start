<?php
namespace Magento\Elasticsearch\Model\Adapter;

/**
 * @deprecated 100.2.0
 * @see \Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface
 */
interface DataMapperInterface
{
    /**
     * Prepare index data for using in search engine metadata
     *
     * @param int $entityId
     * @param array $entityIndexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map($entityId, array $entityIndexData, $storeId, $context = []);
}
