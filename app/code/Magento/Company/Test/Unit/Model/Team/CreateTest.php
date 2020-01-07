<?php
namespace Magento\Company\Test\Unit\Model\Team;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\Data\StructureInterface;
use Magento\Company\Model\Company\Structure;
use Magento\Company\Model\ResourceModel\Team as TeamResource;
use Magento\Company\Model\Team;
use Magento\Company\Model\Team\Create;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit test for \Magento\Company\Model\Team\Create class.
 */
class CreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Create
     */
    private $createCommand;

    /**
     * @var TeamResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $teamResourceMock;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureManagerMock;

    /**
     * @var CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepositoryMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->teamResourceMock = $this->getMockBuilder(TeamResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureManagerMock = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepositoryMock = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->getMockForAbstractClass();

        $this->createCommand = (new ObjectManager($this))->getObject(
            Create::class,
            [
                'teamResource' => $this->teamResourceMock,
                'structureManager' => $this->structureManagerMock,
                'companyRepository' => $this->companyRepositoryMock
            ]
        );
    }

    /**
     * Test for `create` method.
     *
     * @return void
     */
    public function testCreate()
    {
        $companyId = 1;
        $superUserId = 2;
        $teamId = 3;
        $nodeId = 4;

        $team = $this->getMockBuilder(Team::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $team->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturnOnConsecutiveCalls(null, $teamId);

        $company = $this->getMockBuilder(CompanyInterface::class)
            ->getMockForAbstractClass();
        $company->expects($this->atLeastOnce())
            ->method('getSuperUserId')
            ->willReturn($superUserId);
        $this->companyRepositoryMock->expects($this->atLeastOnce())
            ->method('get')
            ->with($companyId)
            ->willReturn($company);

        $tree = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tree->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn($nodeId);
        $this->structureManagerMock->expects($this->atLeastOnce())
            ->method('getTreeByCustomerId')
            ->with($superUserId)
            ->willReturn($tree);

        $this->structureManagerMock->expects($this->once())
            ->method('addNode')
            ->with(
                $teamId,
                StructureInterface::TYPE_TEAM,
                $nodeId
            );

        $this->createCommand->create($team, $companyId);
    }

    /**
     * Test for `create` method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not create team
     */
    public function testCreateWithException()
    {
        $team = $this->getMockBuilder(Team::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $team->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->createCommand->create($team, 1);
    }
}
