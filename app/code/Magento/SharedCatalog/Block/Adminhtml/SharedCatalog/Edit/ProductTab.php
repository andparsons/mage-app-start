<?php
namespace Magento\SharedCatalog\Block\Adminhtml\SharedCatalog\Edit;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Ui\Component\Layout\Tabs\TabWrapper;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class ProductTab
 */
class ProductTab extends TabWrapper implements TabInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var bool
     */
    protected $isAjaxLoaded = false;

    /**
     * UI component factory
     *
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * ProductTab constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param UiComponentFactory $uiComponentFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        UiComponentFactory $uiComponentFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->uiComponentFactory = $uiComponentFactory;
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Linked Products');
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('shared_catalog/sharedCatalog/edit', ['_current' => true]);
    }

    /**
     * Produce and return block's html output
     *
     * This method should not be overridden. You can override _toHtml() method in descendants if needed.
     *
     * @return string
     */
    public function toHtml()
    {
        return (string) $this->uiComponentFactory->create('shared_catalog_listing')->render();
    }
}
