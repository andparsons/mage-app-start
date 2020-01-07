<?php
namespace Magento\Solr\Block\Adminhtml\System\Config;

/**
 * Solr test connection block
 */
class TestConnection extends \Magento\AdvancedSearch\Block\Adminhtml\System\Config\TestConnection
{
    /**
     * {@inheritdoc}
     */
    protected function _getFieldMapping()
    {
        $fields = [
            'engine' => 'catalog_search_engine',
            'hostname' => 'catalog_search_solr_server_hostname',
            'login' => 'catalog_search_solr_server_username',
            'password' => 'catalog_search_solr_server_password',
            'port' => 'catalog_search_solr_server_port',
            'path' => 'catalog_search_solr_server_path',
            'timeout' => 'catalog_search_solr_server_timeout',
        ];
        return array_merge(parent::_getFieldMapping(), $fields);
    }
}
