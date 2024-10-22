<?php

declare(strict_types=1);

namespace Magento\Framework\Image\Adapter;

use Magento\Framework\Image\Test\Unit\Adapter\Gd2Test;

/**
 * Mocking global functions crucial for this adapter
 */

/**
 * @param $paramName
 * @throws \InvalidArgumentException
 * @return string
 */
function ini_get($paramName)
{
    if ('memory_limit' == $paramName) {
        return Gd2Test::$memoryLimit;
    }

    throw new \InvalidArgumentException('Unexpected parameter ' . $paramName);
}

/**
 * @param $file
 * @return mixed
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function getimagesize($file)
{
    return Gd2Test::$imageData;
}

/**
 * @param $file
 * @return mixed
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function filesize($file)
{
    return Gd2Test::$imageSize;
}

/**
 * @param $real
 * @return int
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function memory_get_usage($real)
{
    return 1000000;
}

/**
 * @param $callable
 * @param $param
 * @return bool
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function call_user_func($callable, $param)
{
    return false;
}
