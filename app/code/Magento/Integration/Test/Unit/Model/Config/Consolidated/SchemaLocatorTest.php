<?php
namespace Magento\Integration\Test\Unit\Model\Config\Consolidated;

use Magento\Integration\Model\Config\Consolidated\SchemaLocator;

class SchemaLocatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleReader;

    /** @var string */
    protected $moduleDir;

    /** @var SchemaLocator */
    protected $schemaLocator;

    protected function setUp()
    {
        $this->moduleDir = 'moduleDirectory';
        $this->moduleReader = $this->createMock(\Magento\Framework\Module\Dir\Reader::class);
        $this->moduleReader->expects($this->any())
            ->method('getModuleDir')
            ->willReturn($this->moduleDir);
        $this->schemaLocator = new SchemaLocator($this->moduleReader);
    }

    public function testGetSchema()
    {
        $this->assertEquals($this->moduleDir . '/integration/integration.xsd', $this->schemaLocator->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals(
            $this->moduleDir . '/integration/integration_file.xsd',
            $this->schemaLocator->getPerFileSchema()
        );
    }
}
