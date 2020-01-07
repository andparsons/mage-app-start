<?php
namespace Magento\Solr\Model;

/**
 * Interface \Magento\Solr\Model\AdapterFactoryInterface
 *
 */
interface AdapterFactoryInterface
{
    /**
     * Return search adapter
     *
     * @return \Magento\Solr\Model\Adapter\Solarium
     */
    public function createAdapter();
}
