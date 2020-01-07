<?php
namespace Magento\Catalog\Ui\DataProvider\Product\Related;

/**
 * Class CrossSellDataProvider
 *
 * @api
 * @since 101.0.0
 */
class CrossSellDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    protected function getLinkType()
    {
        return 'cross_sell';
    }
}
