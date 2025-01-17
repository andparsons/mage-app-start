<?php

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

class AssociatedProductGrid extends DataGrid
{
    /**
     * @var string
     */
    protected $selectItem = '.data-grid-cell-content';

    /**
     * @var array
     */
    protected $filters = [
        'sku' => [
            'selector' => '[name="sku"]',
        ],
    ];
}
