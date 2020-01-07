<?php
namespace Magento\Company\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config.
 */
class Config implements \Magento\Company\Api\StatusServiceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    private $xmlPathActive = 'btob/website_configuration/company_active';

    /**
     * @var string
     */
    private $xmlPathStorefrontRegistrationAllowed = 'company/general/allow_company_registration';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive($scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag($this->xmlPathActive, $scopeType, $scopeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isStorefrontRegistrationAllowed($scopeType = ScopeInterface::SCOPE_WEBSITE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag($this->xmlPathStorefrontRegistrationAllowed, $scopeType, $scopeCode);
    }
}
