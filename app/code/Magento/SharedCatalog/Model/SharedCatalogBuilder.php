<?php
namespace Magento\SharedCatalog\Model;

/**
 * Class for build shared catalog object.
 */
class SharedCatalogBuilder
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogFactory
     */
    private $sharedCatalogFactory;

    /**
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param \Magento\SharedCatalog\Model\SharedCatalogFactory $sharedCatalogFactory
     */
    public function __construct(
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        \Magento\SharedCatalog\Model\SharedCatalogFactory $sharedCatalogFactory
    ) {
        $this->sharedCatalogRepository = $sharedCatalogRepository;
        $this->sharedCatalogFactory = $sharedCatalogFactory;
    }

    /**
     * Create or get shared catalog by $sharedCatalogId.
     *
     * @param int $sharedCatalogId [optional]
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \UnexpectedValueException
     */
    public function build($sharedCatalogId = 0)
    {
        if ($sharedCatalogId) {
            $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        } else {
            /** @var \Magento\SharedCatalog\Api\Data\SharedCatalogInterface $sharedCatalog */
            $sharedCatalog = $this->sharedCatalogFactory->create();
        }
        return $sharedCatalog;
    }
}
