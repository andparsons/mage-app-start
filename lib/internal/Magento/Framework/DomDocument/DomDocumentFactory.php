<?php

namespace Magento\Framework\DomDocument;

/**
 * DOM document factory
 */
class DomDocumentFactory
{
    /**
     * Create empty DOM document instance.
     *
     * @return \DOMDocument
     */
    public function create()
    {
        return new \DOMDocument();
    }
}
