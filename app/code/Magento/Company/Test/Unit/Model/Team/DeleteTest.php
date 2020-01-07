<?php
namespace Magento\Company\Test\Unit\Model\Team;

use Magento\Company\Api\Data\StructureInterface;
use Magento\Company\Model\Company\Structure;
use Magento\Company\Model\ResourceModel\Team as TeamResource;
use Magento\Company\Model\StructureRepository;
use Magento\Company\Model\Team;
use Magento\Company\Model\Team\Delete;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for \Magento\Company\Model\Team\Delete class.
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Delete
     */
    private $deleteCommand;

    /**
     * @var TeamResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $teamResourceMock;

    /**
     * @var StructureRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureRepositoryMock;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManagerMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->teamResourceMock = $this->getMockBuilder(TeamResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureRepositoryMock = $this->getMockBuilder(StructureRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureManagerMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->deleteCommand = (new ObjectManager($this))->getObject(
            Delete::class,
            [
                'teamResource' => $this->teamResourceMock,
                'structureRepository' => $this->structureRepositoryMock,
                'structureManager' => $this->structureManagerMock
            ]
        );
    }

    /**
     * Test for `delete` method.
     *
     * @return void
     */
    public function testDelete()
    {
        $structureId = 2;

        $structure = $this->getMockBuilder(StructureInterface::class)
            ->getMockForAbstractClass();
        $structure->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($structureId);
        $this->structureManagerMock->expects($this->atLeastOnce())
            ->method('getStructureByTeamId')
            ->willReturn($structure);

        $this->structureRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($structureId);

        $team = $this->getMockBuilder(Team::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->teamResourceMock->expects($this->once())
            ->method('delete')
            ->with($team);

        $this->deleteCommand->delete($team);
    }

    /**
     * Test for `delete` method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This team has child users or teams aligned to it and cannot be deleted.
     */
    public function testDeleteWithException()
    {
        $teamId = 1;
        $structureId = 2;

        $structure = $this->getMockBuilder(StructureInterface::class)
            ->getMockForAbstractClass();
        $structure->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($structureId);
        $this->structureManagerMock->expects($this->atLeastOnce())
            ->method('getStructureByTeamId')
            ->with($teamId)
            ->willReturn($structure);

        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->getMock();
        $node->expects($this->atLeastOnce())
            ->method('hasChildren')
            ->willReturn(true);
        $this->structureManagerMock->expects($this->atLeastOnce())
            ->method('getTreeById')
            ->with($structureId)
            ->willReturn($node);

        $this->structureRepositoryMock->expects($this->never())
            ->method('deleteById');

        $this->teamResourceMock->expects($this->never())
            ->method('delete');

        $team = $this->getMockBuilder(Team::class)
            ->disableOriginalConstructor()
            ->getMock();
        $team->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($teamId);

        $this->deleteCommand->delete($team);
    }
}
