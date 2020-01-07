<?php

namespace Magento\NegotiableQuote\Block\Adminhtml\AdvancedCheckout\Sales\Order\Create\Sku;

/**
 * Form for adding products by SKU.
 *
 * @api
 * @since 100.0.0
 */
class Add extends \Magento\Backend\Block\Template
{
    /**
     * List type of current block
     */
    const LIST_TYPE = 'add_by_sku';

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Initialize SKU container.
     *
     * @return void
     */
    protected function _construct()
    {
        // Used by JS to tell accordions from each other
        $this->setId('sku');
        /* @see \Magento\AdvancedCheckout\Controller\Adminhtml\Index::_getListItemInfo() */
        $this->setListType(self::LIST_TYPE);
        $this->setDataContainerId('sku_container');
    }

    /**
     * Define ADD and DEL buttons.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'deleteButton',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => '', 'onclick' => 'addBySku.del(this)', 'class' => 'action-delete']
        );

        $this->addChild(
            'addButton',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => 'Add another', 'onclick' => 'addBySku.add()', 'class' => 'add']
        );

        return $this;
    }

    /**
     * Get initialization parameters for JS widget.
     *
     * @return string
     */
    public function getJsInitParams()
    {
        $data = [
            'removeFailedSkuUrl' => $this->getUrl('quotes/quote/removeFailedSku'),
            'removeAllFailedSkusUrl' => $this->getUrl('quotes/quote/removeAllFailed'),
            'addConfiguredUrl' => $this->getUrl('quotes/quote/addConfigured'),
            'fetchConfiguredUrl' => $this->getUrl(
                'quotes/quote/configureProductToAdd',
                ['quote_id' => $this->getRequest()->getParam('quote_id')]
            ),
            'quoteId' => $this->getRequest()->getParam('quote_id')
        ];

        $json = $this->serializer->serialize($data);

        return $json;
    }
}
