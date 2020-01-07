<?php
namespace Magento\SharedCatalog\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class SharedCatalogType implements OptionSourceInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalog
     */
    protected $sharedCatalog;

    /**
     * IsActive constructor.
     *
     * @param \Magento\SharedCatalog\Model\SharedCatalog $sharedCatalog
     */
    public function __construct(\Magento\SharedCatalog\Model\SharedCatalog $sharedCatalog)
    {
        $this->sharedCatalog = $sharedCatalog;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->sharedCatalog->getAvailableTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
