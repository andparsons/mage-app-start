<?php
namespace Magento\Theme\Model\Layout\Config;

/**
 * Page layout config reader
 */
class Reader extends \Magento\Framework\Config\Reader\Filesystem
{
    /**
     * List of identifier attributes for merging
     *
     * @var array
     */
    protected $_idAttributes = ['/page_layouts/layout' => 'id'];
}
