<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Messages;

use Magento\Store\Model\ScopeInterface;

/**
 * Admin notification message in case when shared catalog feature is disabled.
 *
 * @api
 * @since 100.0.0
 */
class Notification extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'messages/notification.phtml';

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $moduleConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var string
     */
    private $storesConfigurationResource = 'Magento_Config::config';

    /**
     * @var string
     */
    private $systemConfigPath = 'adminhtml/system_config/edit';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\SharedCatalog\Api\StatusInfoInterface $moduleConfig
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\SharedCatalog\Api\StatusInfoInterface $moduleConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleConfig = $moduleConfig;
        $this->storeManager = $context->getStoreManager();
    }

    /**
     * Is configuration available for current user.
     *
     * @return bool
     */
    public function isConfigurationAvailable()
    {
        return $this->getAuthorization()->isAllowed($this->storesConfigurationResource);
    }

    /**
     * Get b2b configuration section url.
     *
     * @return string
     */
    public function getConfigurationUrl()
    {
        return $this->getUrl($this->systemConfigPath, ['section' => 'btob']);
    }

    /**
     * Renders block only if shared catalog b2b feature is disabled.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->moduleConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            return '';
        }

        return parent::_toHtml();
    }
}
