<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Configure;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * class AbstractColumn
 */
abstract class AbstractColumn extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepares client save url
     *
     * @param string $clientConfigKey
     * @param string $routePath
     * @return $this
     */
    protected function prepareClientSaveUrl($clientConfigKey, $routePath)
    {
        if (!isset($this->_data['config'][$clientConfigKey])) {
            return $this;
        }
        $this->_data['config'][$clientConfigKey]['saveUrl'] = $this->urlBuilder->getUrl($routePath);

        return $this;
    }
}
