<?php

namespace Magento\Framework\Composer;

use Composer\IO\BufferIO;

/**
 * Class creates BufferIO instance
 */
class BufferIoFactory
{
    /**
     * Creates BufferIO instance
     *
     * @return BufferIO
     */
    public function create()
    {
        return new BufferIO();
    }
}
