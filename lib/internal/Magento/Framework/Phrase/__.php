<?php
declare(strict_types=1);

/**
 * Create value-object \Magento\Framework\Phrase
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @return \Magento\Framework\Phrase
 */
function __()
{
    $argc = func_get_args();

    $text = array_shift($argc);
    if (!empty($argc) && is_array($argc[0])) {
        $argc = $argc[0];
    }

    return new \Magento\Framework\Phrase($text, $argc);
}
