<?php
namespace Magento\SharedCatalog\Ui\Component\Bookmark;

use Magento\SharedCatalog\Api\SharedCatalogManagementInterface;
use Magento\Ui\Component\Bookmark;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\SharedCatalog\Model\Form\Storage\CompanyFactory as CompanyStorageFactory;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Bookmark behaviour at the companies grid of shared catalog.
 */
class Company extends Bookmark
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Company
     */
    protected $storage;

    /**
     * @var SharedCatalogManagementInterface
     */
    protected $catalogManagement;

    /**
     * @param ContextInterface $context
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param CompanyStorageFactory $companyStorageFactory
     * @param SharedCatalogManagementInterface $catalogManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        CompanyStorageFactory $companyStorageFactory,
        SharedCatalogManagementInterface $catalogManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
        $this->catalogManagement = $catalogManagement;
        $this->storage = $companyStorageFactory->create([
            'key' => $context->getRequestParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        $config['current'] = [
            'filters' => [
                'applied' => $this->getAppliedFilters()
            ]
        ];
        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * Get default applied filters.
     *
     * @return array
     */
    protected function getAppliedFilters()
    {
        $filters = [];

        if ($this->hasAssignedCompanies()) {
            $filters['is_current'] = 1;
        } else {
            $id = $this->catalogManagement->getPublicCatalog()->getId();
            $filters[SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM] = $id;
        }

        return $filters;
    }

    /**
     * Has companies assigned to shared catalog.
     *
     * @return bool
     */
    protected function hasAssignedCompanies()
    {
        return count($this->storage->getAssignedCompaniesIds()) > 0;
    }
}
