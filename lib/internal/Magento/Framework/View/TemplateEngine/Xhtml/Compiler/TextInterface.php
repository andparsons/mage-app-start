<?php
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;

/**
 * Interface TextInterface
 */
interface TextInterface
{
    /**
     * Compiles the Element node
     *
     * @param \DOMText $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMText $node, DataObject $processedObject);
}
