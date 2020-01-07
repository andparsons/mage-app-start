<?php
namespace Magento\SharedCatalog\Ui\Component\Listing;

/**
 * Shared catalog product grid columns component.
 */
class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Default columns max order.
     *
     * @var int
     */
    private $defaultColumnsMaxOrder = 100;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory
     */
    private $columnFactory;

    /**
     * @var array
     */
    private $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory $columnFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param array $components [optional]
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\SharedCatalog\Ui\Component\Listing\ColumnFactory $columnFactory,
        \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $columnSortOrder = $this->defaultColumnsMaxOrder;
        foreach ($this->attributeRepository->getList() as $attribute) {
            $config = [];
            if (!isset($this->components[$attribute->getAttributeCode()])) {
                $config['sortOrder'] = ++$columnSortOrder;
                if ($attribute->getIsFilterableInGrid()) {
                    $config['filter'] = $this->retrieveFilterType($attribute->getFrontendInput());
                }
                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
                $column->prepare();
                $this->addComponent($attribute->getAttributeCode(), $column);
            }
        }
        parent::prepare();
    }

    /**
     * Retrieve filter type by $frontendInput.
     *
     * @param string $frontendInput
     * @return string
     */
    private function retrieveFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
