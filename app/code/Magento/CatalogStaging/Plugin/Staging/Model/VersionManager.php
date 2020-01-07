<?php
namespace Magento\CatalogStaging\Plugin\Staging\Model;

use Magento\Catalog\Model\ProductRepository;

class VersionManager
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Staging\Model\VersionManager $subject
     * @param int $versionId
     * @return int
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetCurrentVersionId(
        \Magento\Staging\Model\VersionManager $subject,
        $versionId
    ) {
        $this->productRepository->cleanCache();
        return [$versionId];
    }
}
