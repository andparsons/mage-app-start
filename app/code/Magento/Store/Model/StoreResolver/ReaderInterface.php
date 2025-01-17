<?php
namespace Magento\Store\Model\StoreResolver;

/**
 * Interface \Magento\Store\Model\StoreResolver\ReaderInterface
 *
 */
interface ReaderInterface
{
    /**
     * Retrieve list of stores available for scope
     *
     * @param string $scopeCode
     * @return array
     */
    public function getAllowedStoreIds($scopeCode);

    /**
     * Retrieve default store id
     *
     * @param string $scopeCode
     * @return int
     */
    public function getDefaultStoreId($scopeCode);
}
