<?php

namespace Magento\SharedCatalog\Plugin\Source;

class CustomerGroupPlugin
{
    /**
     * @var SharedCatalogGroupsProcessor
     */
    private $sharedCatalogGroupsProcessor;

    /**
     * CustomerGroupPlugin constructor.
     * @param SharedCatalogGroupsProcessor $sharedCatalogGroupsProcessor
     */
    public function __construct(SharedCatalogGroupsProcessor $sharedCatalogGroupsProcessor)
    {
        $this->sharedCatalogGroupsProcessor = $sharedCatalogGroupsProcessor;
    }

    /**
     * @param \Magento\Framework\Data\OptionSourceInterface $source
     * @param array $groups
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(\Magento\Framework\Data\OptionSourceInterface $source, array $groups)
    {
        $result = $groups;
        $firstElement = current($groups);
        if (isset($firstElement['value']) && !is_array($firstElement['value'])) {
            $result = $this->sharedCatalogGroupsProcessor->prepareGroups($groups);
        }
        return $result;
    }

    /**
     * @param \Magento\Framework\Data\OptionSourceInterface $source
     * @param array $groups
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllOptions(\Magento\Framework\Data\OptionSourceInterface $source, array $groups)
    {
        return $this->sharedCatalogGroupsProcessor->prepareGroups($groups);
    }
}
