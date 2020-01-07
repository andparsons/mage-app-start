<?php

namespace Magento\Framework\Api\Test\Unit\Code\Generator;

/**
 * Class Sample for Proxy and Factory generation
 */
class Sample
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
