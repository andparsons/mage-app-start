<?php
namespace Magento\SharedCatalog\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder as StorageUrlBuilder;

/**
 * Column actions.
 */
class Actions extends Column
{
    /** Url path */
    const SHARED_CATALOG_INDEX_EDIT = 'shared_catalog/sharedCatalog/edit';
    const SHARED_CATALOG_INDEX_DELETE = 'shared_catalog/sharedCatalog/delete';
    const SHARED_CATALOG_INDEX_CONFIGURE = 'shared_catalog/sharedCatalog/wizard';
    const SHARED_CATALOG_INDEX_COMPANIES = 'shared_catalog/sharedCatalog/companies';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param Escaper|null $escaper
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        Escaper $escaper = null
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[SharedCatalogInterface::SHARED_CATALOG_ID])) {
                    $this->prepareDataSourceItem($item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Prepare data source item.
     *
     * @param array $item
     *
     * @return void
     */
    protected function prepareDataSourceItem(&$item)
    {
        $sharedCatalogId = $item[SharedCatalogInterface::SHARED_CATALOG_ID];
        $item[$this->getData('name')] = [
            'configure' => [
                'href' => $this->urlBuilder->getUrl(
                    self::SHARED_CATALOG_INDEX_CONFIGURE,
                    [
                        SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId,
                        StorageUrlBuilder::REQUEST_PARAM_CONFIGURE_KEY => $this->getConfigureKey($item)
                    ]
                ),
                'label' => __('Set Pricing and Structure')
            ],
            'companies' => [
                'href' => $this->urlBuilder->getUrl(
                    self::SHARED_CATALOG_INDEX_COMPANIES,
                    [
                        SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId,
                        StorageUrlBuilder::REQUEST_PARAM_CONFIGURE_KEY => $this->getConfigureKey($item)
                    ]
                ),
                'label' => __('Assign Companies')
            ],
            'edit' => [
                'href' => $this->urlBuilder->getUrl(
                    self::SHARED_CATALOG_INDEX_EDIT,
                    [
                        SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId
                    ]
                ),
                'label' => __('General Settings')
            ],
            'delete' => [
                'href' => $this->urlBuilder->getUrl(
                    self::SHARED_CATALOG_INDEX_DELETE,
                    [
                        SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM => $sharedCatalogId
                    ]
                ),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete "%1"', $this->escaper->escapeHtml($item[SharedCatalogInterface::NAME])),
                    'message' => __('This action cannot be undone. Are you sure you want to delete this catalog?'),
                    '__disableTmpl' => true,
                ]
            ]
        ];
    }

    /**
     * Get edit key for item.
     *
     * @param array $item
     * @return string
     */
    protected function getConfigureKey($item)
    {
        // phpcs:ignore Magento2.Security.InsecureFunction
        return md5(
            implode('', [$item[SharedCatalogInterface::SHARED_CATALOG_ID], microtime()])
        );
    }
}
