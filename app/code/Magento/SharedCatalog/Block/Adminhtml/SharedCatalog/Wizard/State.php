<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Display shared catalog state step.
 *
 * @api
 * @since 100.0.0
 */
class State extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Is catalog configured.
     *
     * @return bool
     */
    public function isCatalogConfigured()
    {
        return $this->getCurrentSharedCatalog()->getStoreId() !== null;
    }

    /**
     * Get current shared catalog.
     *
     * @return \Magento\SharedCatalog\Api\Data\SharedCatalogInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCurrentSharedCatalog()
    {
        $catalogId = $this->getRequest()->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        return $this->sharedCatalogRepository->get($catalogId);
    }
}
