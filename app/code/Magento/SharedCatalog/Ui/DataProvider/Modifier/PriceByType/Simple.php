<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Ui\DataProvider\Modifier\PriceByType;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\SharedCatalog\Model\Form\Storage\PriceCalculator;
use Magento\SharedCatalog\Model\Form\Storage\UrlBuilder;

/**
 * Modifier for simple product.
 */
class Simple implements ModifierInterface
{
    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\PriceCalculator
     */
    private $priceCalculator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param PriceCalculator $priceCalculator
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        PriceCalculator $priceCalculator
    ) {
        $this->request = $request;
        $this->priceCalculator = $priceCalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if (empty($data['entity_id']) || !isset($data['price'])) {
            return $data;
        }
        $data['new_price'] = $this->priceCalculator->calculateNewPriceForProduct(
            $this->request->getParam(UrlBuilder::REQUEST_PARAM_CONFIGURE_KEY),
            $data['sku'],
            $data['price'],
            $data['website_id']
        );

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
