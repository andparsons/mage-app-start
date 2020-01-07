<?php

namespace Magento\Deploy\Console\Command;

/**
 * @param $func
 * @return bool
 */
function function_exists($func)
{
    return $func !== 'pcntl_fork';
}
