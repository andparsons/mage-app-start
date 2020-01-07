<?php

namespace Magento\RequisitionList\Block\Requisition\Item;

use Magento\RequisitionList\Api\Data\RequisitionListItemInterface;

/**
 * Product options block for requisition list item.
 *
 * @api
 * @since 100.0.0
 */
class Options extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $_helperPool;

    /**
     * @var RequisitionListItemInterface
     */
    private $item;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemProduct
     */
    private $requisitionListItemProduct;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItemOptionsLocator
     */
    private $requisitionListItemOptionsLocator;

    /**
     * @var array
     */
    private $ignoreTypes;

    /**
     * List of product options rendering configurations by product type.
     *
     * @var array
     */
    private $_optionsCfg = [
        'default' => [
            'helper' => \Magento\Catalog\Helper\Product\Configuration::class,
            'template' => 'Magento_RequisitionList::requisition/view/items/options_list.phtml',
        ],
    ];

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool
     * @param \Magento\RequisitionList\Model\RequisitionListItemProduct $requisitionListItemProduct
     * @param \Magento\RequisitionList\Model\RequisitionListItemOptionsLocator $requisitionListItemOptionsLocator
     * @param array $ignoreTypes [optional]
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool,
        \Magento\RequisitionList\Model\RequisitionListItemProduct $requisitionListItemProduct,
        \Magento\RequisitionList\Model\RequisitionListItemOptionsLocator $requisitionListItemOptionsLocator,
        array $ignoreTypes = [],
        array $data = []
    ) {
        $this->_helperPool = $helperPool;
        $this->requisitionListItemProduct = $requisitionListItemProduct;
        $this->requisitionListItemOptionsLocator = $requisitionListItemOptionsLocator;
        $this->ignoreTypes = $ignoreTypes;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Initialize product options renderers.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_eventManager->dispatch('product_option_renderer_init', ['block' => $this]);
    }

    /**
     * Set requisition list item.
     *
     * @param RequisitionListItemInterface $item
     * @return $this
     */
    public function setItem(RequisitionListItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get requisition list item.
     *
     * @return RequisitionListItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Adds config for rendering product type options.
     *
     * @param string $productType
     * @param string $helperName
     * @param string|null $template [optional]
     * @return $this
     */
    public function addOptionsRenderCfg($productType, $helperName, $template = null)
    {
        $this->_optionsCfg[$productType] = ['helper' => $helperName, 'template' => $template];
        return $this;
    }

    /**
     * Get item options renderer config.
     *
     * @param string $productType
     * @return array|null
     */
    public function getOptionsRenderCfg($productType)
    {
        if (isset($this->_optionsCfg[$productType])) {
            return $this->_optionsCfg[$productType];
        } elseif (isset($this->_optionsCfg['default'])) {
            return $this->_optionsCfg['default'];
        } else {
            return null;
        }
    }

    /**
     * Retrieve configured product options.
     *
     * @return array
     */
    public function getConfiguredOptions()
    {
        $item = $this->getItem();

        try {
            $product = $this->requisitionListItemProduct->getProduct($item);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }

        if (in_array($product->getTypeId(), $this->ignoreTypes) || !$product->getTypeInstance()->hasOptions($product)) {
            return [];
        }

        $data = $this->getOptionsRenderCfg($product->getTypeId());
        $helper = $this->_helperPool->get($data['helper']);
        $requisitionListItemOptions = $this->requisitionListItemOptionsLocator->getOptions($item);

        return $helper->getOptions($requisitionListItemOptions);
    }

    /**
     * Retrieve block template.
     *
     * @return string
     */
    public function getTemplate()
    {
        $template = parent::getTemplate();
        if ($template) {
            return $template;
        }

        $item = $this->getItem();
        if (!$item) {
            return '';
        }
        if ($this->requisitionListItemProduct->isProductAttached($item)) {
            try {
                $product = $this->requisitionListItemProduct->getProduct($item);
                $data = $this->getOptionsRenderCfg($product->getTypeId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $data = null;
            }
        }
        if (empty($data['template'])) {
            $data = $this->getOptionsRenderCfg('default');
        }

        return empty($data['template']) ? '' : $data['template'];
    }

    /**
     * Render block html.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->requisitionListItemProduct->isProductAttached($this->getItem())) {
            $this->setOptionList($this->getConfiguredOptions());
        }

        return parent::_toHtml();
    }
}
