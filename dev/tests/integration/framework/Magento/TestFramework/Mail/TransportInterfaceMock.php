<?php

namespace Magento\TestFramework\Mail;

class TransportInterfaceMock implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * Mock of send a mail using transport
     *
     * @return void
     */
    public function sendMessage()
    {
        return;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return '';
    }
}
