<?php
namespace Magento\Framework\Filter;

/**
 * Encrypt filter
 */
class Encrypt extends \Zend_Filter_Encrypt
{
    /**
     * @param \Magento\Framework\Filter\Encrypt\AdapterInterface $adapter
     */
    public function __construct(\Magento\Framework\Filter\Encrypt\AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }
}
