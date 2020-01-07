<?php

/**
 * Resource helper class
 */
namespace Magento\Logging\Model\ResourceModel;

class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Construct
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix = 'Logging')
    {
        parent::__construct($resource, $modulePrefix);
    }
}
