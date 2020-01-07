<?php
namespace Magento\NegotiableQuote\Model;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class for working with the module config: checking settings and overall active status.
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var string
     */
    private $xmlPathActive = 'btob/website_configuration/negotiablequote_active';

    /**
     * Xml path file formats
     *
     * @var string
     */
    private $xmlPathFileFormats = 'quote/attached_files/file_formats';

    /**
     * Xml path file size
     *
     * @var string
     */
    private $xmlPathFileSize = 'quote/attached_files/maximum_file_size';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigResource $configResource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigResource $configResource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
    }

    /**
     * Check if the module is active.
     *
     * @param string $scopeType [optional]
     * @param string|null $scopeCode [optional]
     * @return bool
     */
    public function isActive($scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag($this->xmlPathActive, $scopeType, $scopeCode);
    }

    /**
     * Change module activation status to active.
     *
     * @param bool $isActive
     * @param string $scopeType [optional]
     * @param int $scopeId [optional]
     * @return void
     */
    public function setIsActive($isActive, $scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeId = 0)
    {
        $this->configResource->saveConfig($this->xmlPathActive, (bool)$isActive, $scopeType, $scopeId);
    }

    /**
     * Get from store configuration allowed extensions for negotiable quote attachable files.
     *
     * @return string|null
     */
    public function getAllowedExtensions()
    {
        $extensions = $this->scopeConfig->getValue($this->xmlPathFileFormats);
        $extensions = $extensions ? str_replace(' ', '', $extensions) : $extensions;
        return $extensions;
    }

    /**
     * Get from store configuration maximum size for negotiable quote attachable files.
     *
     * @return int|null
     */
    public function getMaxFileSize()
    {
        return $this->scopeConfig->getValue($this->xmlPathFileSize);
    }
}
