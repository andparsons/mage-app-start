<?php
namespace Magento\QuickOrder\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    private $xmlPathActive = 'btob/website_configuration/quickorder_active';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is module active
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return bool
     */
    public function isActive($scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag($this->xmlPathActive, $scopeType, $scopeCode);
    }
}
