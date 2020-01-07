<?php
namespace Magento\SharedCatalog\Model;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Actions with shared catalog.
 */
class Management implements \Magento\SharedCatalog\Api\SharedCatalogManagementInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogFactory
     */
    private $sharedCatalogFactory;

    /**
     * Management constructor.
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogFactory $sharedCatalogFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Model\SharedCatalogFactory $sharedCatalogFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->sharedCatalogFactory = $sharedCatalogFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicCatalog()
    {
        $this->searchCriteriaBuilder->addFilter(SharedCatalogInterface::TYPE, SharedCatalogInterface::TYPE_PUBLIC);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sharedCatalogs = $this->sharedCatalogRepository->getList($searchCriteria);

        if (!$sharedCatalogs->getTotalCount()) {
            throw new NoSuchEntityException(__('No such public catalog entity'));
        }
        $sharedCatalogItems = $sharedCatalogs->getItems();
        return array_shift($sharedCatalogItems);
    }

    /**
     * {@inheritdoc}
     */
    public function isPublicCatalogExist()
    {
        try {
            $this->getPublicCatalog();
            $isExist = true;
        } catch (NoSuchEntityException $e) {
            $isExist = false;
        }

        return $isExist;
    }
}
