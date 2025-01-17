<?php

namespace Magento\Reports\Test\Block\Adminhtml\Product\Lowstock;

/**
 * Class Grid
 * Low Stock Report grid
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="name"]',
        ],
    ];
}
