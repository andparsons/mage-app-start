<?php

namespace Magento\Backend\Test\Unit\Model\Search\Config\Structure\Element\Builder;

use Magento\Backend\Model\Search\Config\Structure\Element\Builder\Group;
use Magento\Config\Model\Config\StructureElementInterface;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var StructureElementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureElementMock;

    /**
     * @var Group
     */
    private $model;

    protected function setUp()
    {
        $this->structureElementMock = $this->getMockForAbstractClass(StructureElementInterface::class);
        $this->model = new Group();
    }

    public function testBuild()
    {
        $structureElementPath = 'path_part_1/path_part_2';

        $this->structureElementMock->expects($this->never())
            ->method('getId');
        $this->structureElementMock->expects($this->once())
            ->method('getPath')
            ->willReturn($structureElementPath);
        $this->assertEquals(
            [
                'section' => 'path_part_1',
                'group'   => 'path_part_2',
            ],
            $this->model->build($this->structureElementMock)
        );
    }
}
