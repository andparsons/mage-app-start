<?php
namespace Magento\Framework\Indexer\Test\Unit\Config;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->urnResolverMock = $this->createMock(\Magento\Framework\Config\Dom\UrnResolver::class);
        $this->model = new \Magento\Framework\Indexer\Config\SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Indexer/etc/indexer_merged.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer_merged.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer_merged.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:magento:framework:Indexer/etc/indexer.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:magento:framework:Indexer/etc/indexer.xsd'),
            $this->model->getPerFileSchema()
        );
    }
}
