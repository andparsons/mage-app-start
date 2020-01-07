<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\Component\Listing\Column\Configure;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder as StorageUrlBuilder;

/**
 * Tier price column component.
 */
class TierPrice extends AbstractColumn
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\SharedCatalog\Model\Form\Storage\UrlBuilder $urlBuilder,
        ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\SharedCatalog\Model\Form\Storage\WizardFactory $wizardStorageFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $urlBuilder, $components, $data);
        $this->attributeRepository = $attributeRepository;
        $this->wizardStorageFactory = $wizardStorageFactory;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        $storeId = $this->getStoreId();
        if (isset($dataSource['data']['items'])) {
            $allowedProductTypes = (array)$this->attributeRepository->get('tier_price')->getApplyTo();
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (in_array($item['type_id'], $allowedProductTypes)) {
                    $item['has_tier_prices'] = true;
                    $item[$fieldName]['configure'] = [
                        'callback' => [
                            [
                                'provider' => 'tier_price_modal.tier_price_form_renderer',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'tier_price_modal',
                                'target' => 'openModal',
                            ],
                            [
                                'provider' => 'tier_price_modal.tier_price_form_renderer',
                                'target' => 'render',
                                'params' => [
                                    'product_id' => $item['entity_id'],
                                    'store_id' => $storeId
                                ]
                            ]
                        ],
                        'label' => $this->getLabel($item),
                        'hidden' => false,
                    ];
                } else {
                    $item['has_tier_prices'] = false;
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get shared catalog store ID from storage.
     *
     * @return int
     */
    private function getStoreId()
    {
        $urlKey = $this->request->getParam(StorageUrlBuilder::REQUEST_PARAM_CONFIGURE_KEY);
        /** @var \Magento\SharedCatalog\Model\Form\Storage\Wizard $storage */
        $storage = $this->wizardStorageFactory->create(['key' => $urlKey]);

        return $storage->getStoreId();
    }

    /**
     * Retrieve label.
     *
     * @param array $itemData
     * @return \Magento\Framework\Phrase|string
     */
    private function getLabel(array $itemData)
    {
        $label = __('Configure');
        $tierPriceCount = $itemData['tier_price_count'];
        if ($tierPriceCount > 0) {
            $label .= sprintf(' (%s)', $tierPriceCount);
        }
        return $label;
    }
}
