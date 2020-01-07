<?php
namespace Magento\Config\Model\Config\Backend\Design;

/**
 * @api
 * @since 100.0.2
 */
class Exception extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'core_config_backend_design_exception';
}
