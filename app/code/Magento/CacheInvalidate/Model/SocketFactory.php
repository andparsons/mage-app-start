<?php
namespace Magento\CacheInvalidate\Model;

class SocketFactory
{
    /**
     * @return \Zend\Http\Client\Adapter\Socket
     */
    public function create()
    {
        return new \Zend\Http\Client\Adapter\Socket();
    }
}
