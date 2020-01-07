<?php

namespace Magento\SharedCatalog\Plugin\Source;

/**
 * Plugin for change сustomer group option array.
 */
class CustomerGroupReportPlugin
{
    /**
     * @var SharedCatalogGroupsProcessor
     */
    private $sharedCatalogGroupsProcessor;

    /**
     * @param SharedCatalogGroupsProcessor $sharedCatalogGroupsProcessor
     */
    public function __construct(SharedCatalogGroupsProcessor $sharedCatalogGroupsProcessor)
    {
        $this->sharedCatalogGroupsProcessor = $sharedCatalogGroupsProcessor;
    }

    /**
     * Change customer group array to grouped options after toOptionArray method.
     *
     * @param \Magento\Framework\Data\OptionSourceInterface $source
     * @param array $groups
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(\Magento\Framework\Data\OptionSourceInterface $source, array $groups)
    {
        $result = [];
        foreach ($groups as $value => $label) {
            $result[$value] = ['value' => $value, 'label' => $label];
        }
        $firstElement = reset($result);
        if (isset($firstElement['value']) && !is_array($firstElement['value'])) {
            $result = $this->sharedCatalogGroupsProcessor->prepareGroups($result);
        }
        return $result;
    }
}
