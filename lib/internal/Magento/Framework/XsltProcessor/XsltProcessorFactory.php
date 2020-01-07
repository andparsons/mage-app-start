<?php
namespace Magento\Framework\XsltProcessor;

/**
 * XSLTProcessor document factory
 */
class XsltProcessorFactory
{
    /**
     * Create empty XSLTProcessor instance.
     *
     * @return \XSLTProcessor
     */
    public function create()
    {
        return new \XSLTProcessor();
    }
}
