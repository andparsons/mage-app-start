<?php
namespace Magento\SharedCatalog\Ui\Component\Listing\Pricing;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;
use Magento\Ui\Component\Container;

/**
 * Price storage component.
 */
class PriceStorage extends Container
{
    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UrlBuilder $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UrlBuilder $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @inheritDoc
     */
    public function prepare()
    {
        $this->prepareSaveUrl();
        parent::prepare();
    }

    /**
     * Prepare save url for editing client.
     *
     * @return $this
     */
    private function prepareSaveUrl()
    {
        if (!isset($this->_data['config']['clientConfig'])) {
            return $this;
        }
        $url = $this->urlBuilder->getUrl('shared_catalog/sharedCatalog_configure_product_price/save');
        $this->_data['config']['clientConfig']['saveUrl'] = $url;

        return $this;
    }
}
