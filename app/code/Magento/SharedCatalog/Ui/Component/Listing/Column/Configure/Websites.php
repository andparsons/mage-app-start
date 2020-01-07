<?php
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Configure;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Websites column component.
 */
class Websites extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name.
     */
    const NAME = 'websites';

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Columns\Websites
     */
    private $catalogWebsitesColumn;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\Catalog\Ui\Component\Listing\Columns\Websites $catalogWebsitesColumn
     * @param array $components [optional]
     * @param array $data [optional]
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\Catalog\Ui\Component\Listing\Columns\Websites $catalogWebsitesColumn,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->catalogWebsitesColumn = $catalogWebsitesColumn;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $this->catalogWebsitesColumn->setData($this->getData());
        return $this->catalogWebsitesColumn->prepareDataSource($dataSource);
    }

    /**
     * Prepare component configuration.
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->storeManager->isSingleStoreMode()) {
            $this->_data['config']['componentDisabled'] = true;
        } else {
            $sharedCatalogId = $this->context->getRequestParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
            if (isset($sharedCatalogId) && !$this->isSharedCatalogScopeGlobal(intval($sharedCatalogId))) {
                $this->_data['config']['componentDisabled'] = true;
            }
        }
    }

    /**
     * Check if shared catalog scope is global.
     *
     * @param int $sharedCatalogId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isSharedCatalogScopeGlobal($sharedCatalogId)
    {
        $storeId = $this->sharedCatalogRepository->get($sharedCatalogId)->getStoreId();
        return empty($storeId);
    }
}
