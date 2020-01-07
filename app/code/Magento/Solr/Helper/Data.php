<?php
namespace Magento\Solr\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Solr\Model\AdapterFactoryInterface;
use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;

/**
 * Solr search helper
 */
class Data extends AbstractHelper implements ClientOptionsInterface
{
    /**
     * Current adapter name
     */
    const SOLR = 'solr';

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var AdapterFactoryInterface
     */
    private $adapterFactory;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param AdapterFactoryInterface $adapterFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        AdapterFactoryInterface $adapterFactory
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
        $this->adapterFactory = $adapterFactory;
    }

    /**
     * Retrieve information from Solr search engine configuration
     *
     * @param string $field
     * @param int $storeId
     * @return string|int
     */
    public function getSolrConfigData($field, $storeId = null)
    {
        return $this->getSearchConfigData('solr_' . $field, $storeId);
    }

    /**
     * Retrieve information from search engine configuration
     *
     * @param string $field
     * @param int|null $storeId
     * @return string|int
     */
    public function getSearchConfigData($field, $storeId = null)
    {
        $path = 'catalog/search/' . $field;
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Return true if third party search engine is used
     *
     * @return bool
     */
    public function isSolrEnabled()
    {
        return $this->getSearchConfigData('engine') == self::SOLR;
    }

    /**
     * Check if enterprise engine is available
     *
     * @return bool
     */
    public function isActiveEngine()
    {
        return $this->adapterFactory->createAdapter()
            ->ping();
    }

    /**
     * Return search client options
     *
     * @param array $options
     * @return mixed
     */
    public function prepareClientOptions($options = [])
    {
        $defaultOptions = [
            'hostname' => $this->getSolrConfigData('server_hostname'),
            'login' => $this->getSolrConfigData('server_username'),
            'password' => $this->getSolrConfigData('server_password'),
            'port' => $this->getSolrConfigData('server_port'),
            'timeout' => $this->getSolrConfigData('server_timeout') ?: 15,
            'path' => $this->getSolrConfigData('server_path'),
        ];
        $options = array_merge($defaultOptions, $options);
        return $options;
    }
}
