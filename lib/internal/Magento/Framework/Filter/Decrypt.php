<?php
namespace Magento\Framework\Filter;

/**
 * Decrypt filter
 */
class Decrypt extends \Zend_Filter_Decrypt
{
    /**
     * @param \Magento\Framework\Filter\Encrypt\AdapterInterface $adapter
     */
    public function __construct(\Magento\Framework\Filter\Encrypt\AdapterInterface $adapter)
    {
        $this->setAdapter($adapter);
    }
}
