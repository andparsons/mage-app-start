<?php

namespace Magento\Framework\Test\Unit\DomDocument;

use Magento\Framework\DomDocument\DomDocumentFactory;

class DomDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateReturnsDomDocument()
    {
        $domDocumentFactory = new DomDocumentFactory();
        $this->assertInstanceOf(
            \DOMDocument::class,
            $domDocumentFactory->create()
        );
    }
}
