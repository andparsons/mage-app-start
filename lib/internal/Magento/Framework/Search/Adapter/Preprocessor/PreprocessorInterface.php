<?php
namespace Magento\Framework\Search\Adapter\Preprocessor;

/**
 * Interface \Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface
 *
 */
interface PreprocessorInterface
{
    /**
     * @param string $query
     * @return string
     */
    public function process($query);
}
