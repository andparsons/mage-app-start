<?php
namespace Magento\SharedCatalog\Block\Adminhtml\System\Config\CategoryPermissions;

/**
 * Disables category permissions system configuration control.
 */
class IsActive extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    private $xmlPathActiveSharedCatalog = 'btob/website_configuration/sharedcatalog_active';

    /**
     * @var \Magento\SharedCatalog\Api\StatusInfoInterface
     */
    private $statusSharedCatalog;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\SharedCatalog\Api\StatusInfoInterface $statusSharedCatalog
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\SharedCatalog\Api\StatusInfoInterface $statusSharedCatalog,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->statusSharedCatalog = $statusSharedCatalog;
    }

    /**
     * @inheritdoc
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->isSharedCatalogFeatureEnabled()) {
            $element->setDisabled(true);
        }

        return $element->getElementHtml();
    }

    /**
     * Check is shared catalog b2b feature enabled.
     *
     * @return bool
     */
    private function isSharedCatalogFeatureEnabled()
    {
        $sharedCatalogEnabled = $this->_scopeConfig->isSetFlag($this->xmlPathActiveSharedCatalog);

        return $sharedCatalogEnabled || !empty($this->statusSharedCatalog->getActiveSharedCatalogStoreIds());
    }
}
