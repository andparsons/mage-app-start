<?php

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Attributes;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Product attributes grid.
 */
class Grid extends DataGrid
{
    /**
     * Grid fields map
     *
     * @var array
     */
    protected $filters = [
        'label' => [
            'selector' => '[name="frontend_label"]',
        ]
    ];
}
