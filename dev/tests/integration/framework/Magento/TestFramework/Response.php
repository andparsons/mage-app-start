<?php

/**
 * HTTP response implementation that is used instead core one for testing
 */
namespace Magento\TestFramework;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Response extends \Magento\Framework\App\Response\Http
{
    public function sendResponse()
    {
    }
}
