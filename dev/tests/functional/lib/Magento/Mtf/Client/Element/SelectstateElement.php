<?php
declare(strict_types=1);

namespace Magento\Mtf\Client\Element;

/**
 * @inheritdoc
 */
class SelectstateElement extends SelectElement
{
    /**
     * @inheritdoc
     */
    protected $optionByValue = './/option[normalize-space(.)=%s]';
}
