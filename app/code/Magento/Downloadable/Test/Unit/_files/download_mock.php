<?php
namespace Magento\Downloadable\Helper;

use Magento\Downloadable\Test\Unit\Helper\DownloadTest;

/**
 * @return bool
 */
function function_exists()
{
    return DownloadTest::$functionExists;
}

/**
 * @return string
 */
function mime_content_type()
{
    return DownloadTest::$mimeContentType;
}
