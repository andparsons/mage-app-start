<?php
namespace Magento\Framework\Setup\Test\Unit\Declaration\Schema\Db\MySQL\Definition\Columns;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Boolean;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Comment;
use Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Nullable;
use Magento\Framework\Setup\Declaration\Schema\Dto\Columns\Boolean as BooleanColumn;

class BooleanTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Setup\Declaration\Schema\Db\MySQL\Definition\Columns\Boolean
     */
    private $boolean;

    /**
     * @var Nullable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nullableMock;

    /**
     * @var Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $commentMock;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->nullableMock = $this->getMockBuilder(Nullable::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentMock = $this->getMockBuilder(Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->boolean = $this->objectManager->getObject(
            Boolean::class,
            [
                'nullable' => $this->nullableMock,
                'comment' => $this->commentMock,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Test conversion to definition.
     */
    public function testToDefinition()
    {
        /** @var BooleanColumn|\PHPUnit_Framework_MockObject_MockObject $column */
        $column = $this->getMockBuilder(BooleanColumn::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column->expects($this->any())
            ->method('getName')
            ->willReturn('col');
        $column->expects($this->any())
            ->method('getType')
            ->willReturn('blob');
        $column->expects($this->any())
            ->method('getDefault')
            ->willReturn(0);
        $adapterMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnectionMock->expects($this->once())->method('getConnection')->willReturn($adapterMock);
        $adapterMock->expects($this->once())
            ->method('quoteIdentifier')
            ->with('col')
            ->willReturn('`col`');
        $this->nullableMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('NULL');
        $this->commentMock->expects($this->any())
            ->method('toDefinition')
            ->with($column)
            ->willReturn('COMMENT "Comment"');
        $this->assertEquals(
            '`col` BOOLEAN NULL DEFAULT 0 COMMENT "Comment"',
            $this->boolean->toDefinition($column)
        );
    }

    /**
     * Test from definition conversion.
     */
    public function testFromDefinitionTinyInt()
    {
        $expectedData = [
            'type' => 'boolean',
            'unsigned' => false,
            'default' => true,
        ];
        $result = $this->boolean->fromDefinition(
            [
                'type' => 'tinyint',
                'padding' => '1',
                'default' => '1'
            ]
        );
        $this->assertEquals($expectedData, $result);
        $expectedData = [
            'type' => 'boolean',
            'unsigned' => false,
        ];
        $result = $this->boolean->fromDefinition(
            [
                'type' => 'tinyint',
                'padding' => '1',
            ]
        );
        $this->assertEquals($expectedData, $result);
    }
}
