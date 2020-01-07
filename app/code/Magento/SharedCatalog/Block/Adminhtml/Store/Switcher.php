<?php
namespace Magento\SharedCatalog\Block\Adminhtml\Store;

/**
 * Class Switcher
 */
class Switcher extends \Magento\Backend\Block\Template
{
    /**
     * Id for all store views
     */
    const ALL_STORES_ID = '0';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
        $this->systemStore = $systemStore;
    }

    /**
     * Get stores list
     *
     * @return \Magento\Store\Api\Data\GroupInterface[]
     */
    public function getStoreList()
    {
        return $this->systemStore->getGroupCollection();
    }

    /**
     * Get stores options as array
     *
     * @return array
     */
    public function getStoreOptionsAsArray()
    {
        $options[] = [
            'id' => self::ALL_STORES_ID,
            'label' => __('All Stores')
        ];
        /**
         * @var int $id
         * @var \Magento\Store\Api\Data\GroupInterface $store
         */
        foreach ($this->getStoreList() as $store) {
            $options[] = [
                'id' => $store->getId(),
                'label' => $store->getName()
            ];
        }
        return $options;
    }

    /**
     * Get stores options as JSON
     *
     * @return string
     */
    public function getStoreOptionsAsJson()
    {
        return $this->jsonEncoder->encode($this->getStoreOptionsAsArray());
    }
}
