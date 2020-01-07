<?php
namespace Magento\SharedCatalog\Ui\Component\Listing;

/**
 * Factory for creation of product attribute columns.
 */
class ColumnFactory
{
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    private $componentFactory;

    /**
     * @var array
     */
    private $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    private $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'date' => 'date',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     */
    public function __construct(\Magento\Framework\View\Element\UiComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * Create component object.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param array $config [optional]
     * @return \Magento\Ui\Component\Listing\Columns\ColumnInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        array $config = []
    ) {
        $columnName = $attribute->getAttributeCode();
        $config = array_merge([
            'label' => __($attribute->getDefaultFrontendLabel()),
            'dataType' => $this->retrieveDataType($attribute),
            'add_field' => true,
            'visible' => false,
            'filter' => ($attribute->getIsFilterableInGrid())
                ? $this->retrieveFilterType($attribute->getFrontendInput())
                : null,
        ], $config);

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
        }

        $config['component'] = $this->jsComponentMap[$config['dataType']];

        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    /**
     * Retrieve attribute data type.
     *
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return string
     */
    private function retrieveDataType(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute)
    {
        return isset($this->dataTypeMap[$attribute->getFrontendInput()])
            ? $this->dataTypeMap[$attribute->getFrontendInput()]
            : $this->dataTypeMap['default'];
    }

    /**
     * Retrieve filter type by $frontendInput.
     *
     * @param string $frontendInput
     * @return string
     */
    private function retrieveFilterType($frontendInput)
    {
        $filtersMap = ['date' => 'dateRange'];
        $result = array_replace_recursive($this->dataTypeMap, $filtersMap);
        return isset($result[$frontendInput]) ? $result[$frontendInput] : $result['default'];
    }
}
