<?php
namespace Magento\Logging\Model\ResourceModel\Grid;

class ActionsGroup implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Logging\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\Logging\Model\Config $config
     */
    public function __construct(\Magento\Logging\Model\Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Get options as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_config->getLabels();
    }
}
