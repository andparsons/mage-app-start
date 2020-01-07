<?php
namespace Magento\RequisitionList\Model;

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
    private $scopeConfig;

    /**
     * @var string
     */
    private $xmlPathActive = 'btob/website_configuration/requisition_list_active';

    /**
     * @var string
     */
    private $xmlPathMaxCountRequisitionList = 'requisitionlist/general/number_requisition_lists';

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

    /**
     * Get max value of requisition list creation
     *
     * @return int
     */
    public function getMaxCountRequisitionList()
    {
        return (int)$this->scopeConfig->getValue(
            $this->xmlPathMaxCountRequisitionList,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
