<?php
namespace Magento\Framework\DataObject;

class Factory
{
    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function create(array $data = [])
    {
        return new \Magento\Framework\DataObject($data);
    }
}
