<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Category;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Catalog configure category tree.
 */
class Tree extends \Magento\Backend\Block\Template
{
    /**#@+
     * Category tree routes.
     */
    const TREE_INIT_ROUTE = 'shared_catalog/sharedCatalog/configure_tree_get';
    /**#@-*/

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * Constructor.
     *
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
        return $this->urlBuilder->getUrl(static::TREE_INIT_ROUTE, [
            '_query' => [
                'filters' => [
                    'shared_catalog' => [
                        'id' => $this->getSharedCatalog()->getId()
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get shared catalog.
     * Throw exception if there is no shared catalog for catalog ID in the request.
     *
     * @return SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSharedCatalog()
    {
        $sharedCatalogId = $this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        return $this->sharedCatalogRepository->get($sharedCatalogId);
    }
}
