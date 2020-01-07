<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\State\Category;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Display shared catalog categories tree at catalog structure step.
 *
 * @api
 * @since 100.0.0
 */
class Tree extends \Magento\Backend\Block\Template
{
    /**#@+
     * Category tree routes
     */
    const TREE_INIT_ROUTE = 'shared_catalog/sharedCatalog/configure_tree_pricing_get';
    /**#@-*/

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlBuilder = $urlBuilder;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Get URL for retrieving shared catalog category tree structure.
     * Throw exception if it cannot obtain shared catalog.
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTreeUrl()
    {
        $catalogId = $this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        return $this->urlBuilder->getUrl(self::TREE_INIT_ROUTE, [
            '_query' => [
                'filters' => [
                    'store' => [
                        'id' => $this->sharedCatalogRepository->get($catalogId)->getStoreId()
                    ],
                    'shared_catalog' => [
                        'id' => $catalogId
                    ]
                ]
            ]
        ]);
    }
}
