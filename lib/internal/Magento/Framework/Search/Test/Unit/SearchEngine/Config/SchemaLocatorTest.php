<?php
namespace Magento\Framework\Search\Test\Unit\SearchEngine\Config;

use Magento\Framework\Search\SearchEngine\Config\SchemaLocator;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SchemaLocator
     */
    private $model;

    protected function setUp()
    {
        $urnResolver = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $urnResolver->expects($this->any())
            ->method('getRealPath')
            ->with(SchemaLocator::SEARCH_ENGINE_XSD_PATH)
            ->willReturn('xsd/path');

        $this->model = new SchemaLocator($urnResolver);
    }

    public function testGetSchema()
    {
        $this->assertEquals('xsd/path', $this->model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('xsd/path', $this->model->getPerFileSchema());
    }
}
