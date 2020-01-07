<?php
namespace Magento\Framework\HTTP\Adapter;

/**
 * Override global PHP function
 *
 * @SuppressWarnings("unused")
 * @param mixed $resource
 * @return string
 */
function curl_exec($resource)
{
    return call_user_func(\Magento\Framework\HTTP\Test\Unit\Adapter\CurlTest::$curlExectClosure);
}
