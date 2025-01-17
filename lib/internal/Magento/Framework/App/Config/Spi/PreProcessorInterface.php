<?php
namespace Magento\Framework\App\Config\Spi;

/**
 * Allows to use custom callbacks and functions before applying fallback
 */
interface PreProcessorInterface
{
    /**
     * Pre-processing of config
     *
     * @param array $config
     * @return array
     */
    public function process(array $config);
}
