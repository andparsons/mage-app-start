<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Wizard\Store;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Display store switcher control.
 *
 * @api
 * @since 100.0.0
 */
class Switcher extends \Magento\SharedCatalog\Block\Adminhtml\Store\Switcher
{
    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface
     */
    private $sharedCatalogRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface $sharedCatalogRepository,
        array $data = []
    ) {
        parent::__construct($context, $systemStore, $jsonEncoder, $data);
        $this->sharedCatalogRepository = $sharedCatalogRepository;
    }

    /**
     * Is options selected.
     *
     * @return bool
     */
    public function isOptionSelected()
    {
        return $this->getSelectedOptionValue() !== null;
    }

    /**
     * Get selected option value.
     * Throw exception if there is no shared catalog found.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSelectedOptionValue()
    {
        $sharedCatalogId = $this->_request->getParam(SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM);
        $sharedCatalog = $this->sharedCatalogRepository->get($sharedCatalogId);
        return $sharedCatalog->getStoreId();
    }

    /**
     * Get selected option label.
     *
     * @return string|null
     */
    public function getSelectedOptionLabel()
    {
        $selectedOptionId = $this->getSelectedOptionValue();
        $options = $this->getStoreOptionsAsArray();
        foreach ($options as $option) {
            if ($option['id'] === $selectedOptionId) {
                return $option['label'];
            }
        }
        return null;
    }
}
