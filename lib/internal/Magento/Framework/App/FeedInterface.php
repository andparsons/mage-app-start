<?php
declare(strict_types=1);

namespace Magento\Framework\App;

/**
 * Feed interface
 */
interface FeedInterface
{
    /**
     * Returns the formatted feed content
     *
     * @return string
     */
    public function getFormattedContent() : string;
}
