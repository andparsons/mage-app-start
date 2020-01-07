<?php
namespace Magento\SharedCatalog\Ui\Component\Form\Field;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CustomerGroup.
 */
class CustomerGroup extends \Magento\SharedCatalog\Ui\Component\Form\Field
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
     */
    private $catalogManagement;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface
     */
    private $groupManagement;

    /**
     * @var \Magento\SharedCatalog\Model\Config
     */
    private $moduleConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $catalogManagement
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\SharedCatalog\Model\Config $moduleConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array|\Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $catalogManagement,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\SharedCatalog\Model\Config $moduleConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $components,
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->catalogManagement = $catalogManagement;
        $this->groupManagement = $groupManagement;
        $this->moduleConfig = $moduleConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get field config default data.
     *
     * @return array
     */
    protected function getConfigDefaultData()
    {
        $data = ['value' => $this->getDefaultValue()];
        $website = $this->storeManager->getWebsite()->getId();
        if (!$this->moduleConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            $data['notice'] = null;
        }
        return $data;
    }

    /**
     * Get default field value.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultValue()
    {
        $defaultValue = null;
        $website = $this->storeManager->getWebsite()->getId();
        if ($this->moduleConfig->isActive(ScopeInterface::SCOPE_WEBSITE, $website)) {
            try {
                $publicCatalog = $this->catalogManagement->getPublicCatalog();
                $defaultValue = $publicCatalog->getCustomerGroupId();
            } catch (NoSuchEntityException $e) {
                $defaultValue = null;
            }
        }
        if (!$defaultValue) {
            $defaultValue = $this->groupManagement->getDefaultGroup()->getId();
        }

        return $defaultValue;
    }
}
