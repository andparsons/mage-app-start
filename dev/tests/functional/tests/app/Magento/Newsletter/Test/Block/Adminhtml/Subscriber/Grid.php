<?php

namespace Magento\Newsletter\Test\Block\Adminhtml\Subscriber;

/**
 * Newsletter subscribers grid.
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'email' => [
            'selector' => '#subscriberGrid_filter_email',
        ],
        'firstname' => [
            'selector' => '#subscriberGrid_filter_firstname',
        ],
        'lastname' => [
            'selector' => '#subscriberGrid_filter_lastname',
        ],
        'status' => [
            'selector' => '#subscriberGrid_filter_status',
            'input' => 'select',
        ],
    ];
}
