<?php

namespace Magento\Company\Test\Unit\Model\Company;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyCustomerInterface;
use Magento\Company\Api\Data\StructureInterfaceFactory;
use Magento\Company\Api\Data\StructureSearchResultsInterface;
use Magento\Company\Api\Data\TeamInterface;
use Magento\Company\Api\Data\TeamSearchResultsInterface;
use Magento\Company\Api\TeamRepositoryInterface;
use Magento\Company\Model\Company\Structure as CompanyStructure;
use Magento\Company\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Company\Model\ResourceModel\Structure\Tree as StructureTree;
use Magento\Company\Model\Structure;
use Magento\Company\Model\StructureFactory;
use Magento\Company\Model\StructureRepository;
use Magento\Company\Model\Team;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Unit tests for Magento\Company\Model\Company\Structure class.
 *
 * @package Magento\Company\Test\Unit\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StructureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CompanyStructure
     */
    private $companyStructure;

    /**
     * @var StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureFactory;

    /**
     * @var Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structure;

    /**
     * @var StructureTree|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tree;

    /**
     * @var StructureRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var TeamRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $teamRepositoryInterface;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryInterface;

    /**
     * @var StructureSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureList;

    /**
     * @var CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Framework\Data\Tree\Node|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceNodeMock;

    /**
     * @var \Magento\Framework\Data\Tree\Node|\PHPUnit_Framework_MockObject_MockObject
     */
    private $targetNodeMock;

    /**
     * @var \Magento\Framework\Data\Tree\Node|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceChildNodeMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyRepository = $this->getMockBuilder(CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->structureFactory = $this->getMockBuilder(StructureInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->structure = $this->getMockBuilder(Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tree = $this->getMockBuilder(StructureTree::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureRepository = $this->getMockBuilder(StructureRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->structureList = $this->getMockBuilder(StructureSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $dataObjectHelper = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryInterface = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->teamRepositoryInterface = $this->getMockBuilder(TeamRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $companyCustomerCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceNodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $this->targetNodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $this->sourceChildNodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();

        $this->objectManager = new ObjectManager($this);
        $this->companyStructure = $this->objectManager->getObject(
            CompanyStructure::class,
            [
                'tree' => $this->tree,
                'structureFactory' => $this->structureFactory,
                'structureRepository' => $this->structureRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'teamRepository' => $this->teamRepositoryInterface,
                'customerRepositoryInterface' => $this->customerRepositoryInterface,
                'dataObjectHelper' => $dataObjectHelper,
                'companyCustomerCollectionFactory' => $companyCustomerCollectionFactory,
                'companyRepository' => $this->companyRepository
            ]
        );
    }

    /**
     * Unit tests for some CompanyStructure methods.
     *
     * @param array $array
     * @param string $field
     * @param array $calls
     * @return void
     *
     * @covers \Magento\Company\Model\Company\Structure::addDataToTree()
     * @covers \Magento\Company\Model\Company\Structure::getAllowedIds()
     * @covers \Magento\Company\Model\Company\Structure::getStructureByTeamId()
     * @covers \Magento\Company\Model\Company\Structure::getTreeByCustomerId()
     * @dataProvider addDataToTreeDataProvider
     */
    public function testAddDataToTree(array $array, $field, array $calls)
    {
        $tree = $this->objectManager->getObject(Tree::class);
        $treeNode = $this->objectManager->getObject(
            Node::class,
            [
                'data' => $array,
                'idField' => $field,
                'tree' => $tree,
            ]
        );
        $this->tree->expects($this->atLeastOnce())->method('loadNode')->willReturn($treeNode);
        $this->structureRepository->expects($this->atLeastOnce())->method('get')->willReturn($this->structure);
        $this->getStructureByCustomerId();
        $this->getSearchCriteria();
        $this->prepareCustomerList($calls);
        $this->prepareTeamList($calls);

        $this->companyStructure->addDataToTree($treeNode);
        $this->companyStructure->getAllowedIds(1);
        $this->companyStructure->getStructureByTeamId(1);
        $this->companyStructure->getTreeByCustomerId(1);
    }

    /**
     * Prepare customer list.
     *
     * @param array $calls
     * @return void
     */
    private function prepareCustomerList(array $calls)
    {
        $customerList = $this->getMockBuilder(CustomerSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRepositoryInterface->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($customerList);
        $customer = $this->getCustomerMock($calls);
        $customerList->expects($this->atLeastOnce())->method('getItems')->willReturn([$customer]);
    }

    /**
     * Prepare team list.
     *
     * @param array $calls
     * @return void
     */
    private function prepareTeamList(array $calls)
    {
        $teamList = $this->getMockBuilder(TeamSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->teamRepositoryInterface->expects($this->atLeastOnce())->method('getList')->willReturn($teamList);

        $team = $this->getMockBuilder(Team::class)->disableOriginalConstructor()->getMock();
        $team->expects($this->exactly($calls['team-methods']))->method('getId')->willReturn(1);
        $team->expects($this->exactly($calls['team-methods']))->method('getData')->willReturn(['id' => 1]);
        $teamList->expects($this->atLeastOnce())->method('getItems')->willReturn([$team]);
    }

    /**
     * Retrieve prepared customer mock.
     *
     * @param array $calls
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getCustomerMock(array $calls)
    {
        $companyAttributes = $this->getMockBuilder(CompanyCustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerExtension = $this->getMockBuilder(CustomerExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCompanyAttributes', 'getCompanyAttributes'])
            ->getMockForAbstractClass();
        $customerExtension->expects($this->exactly($calls['user-methods']))
            ->method('getCompanyAttributes')
            ->willReturn($companyAttributes);
        $customer = $this->getMockBuilder(Customer::class)->disableOriginalConstructor()->getMock();
        $customer->expects($this->exactly($calls['user-methods']))
            ->method('getExtensionAttributes')
            ->willReturn($customerExtension);
        $customer->expects($this->exactly($calls['user-methods']))
            ->method('getId')->willReturn(1);
        $customer->expects($this->exactly($calls['user-methods']))
            ->method('__toArray')->willReturn(['id' => 1]);
        return $customer;
    }

    /**
     * Data Provider for method testAddDataToTree.
     *
     * @return array
     */
    public function addDataToTreeDataProvider()
    {
        return [
            [
                [
                    'entity_id' => 1,
                    'entity_type' => 0
                ],
                'user',
                'calls' => [
                    'user-methods' => 1,
                    'team-methods' => 0,
                ]
            ],
            [
                [
                    'entity_id' => 1,
                    'entity_type' => 1
                ],
                'team',
                'calls' => [
                    'user-methods' => 0,
                    'team-methods' => 1,
                ]
            ]
        ];
    }

    /**
     * Test for getAllowedChildrenIds method.
     *
     * @return void
     */
    public function testGetAllowedChildrenIds()
    {
        $entityId = 1;
        $parentId = 1;
        $this->structure->expects($this->atLeastOnce())->method('getEntityId')->willReturn($entityId);
        $this->structureList->expects($this->atLeastOnce())->method('getItems')->willReturn([$this->structure]);
        $this->structureRepository->expects($this->atLeastOnce())->method('getList')->willReturn($this->structureList);
        $this->getSearchCriteria();
        $this->assertEquals([$entityId], $this->companyStructure->getAllowedChildrenIds($parentId));
    }

    /**
     * Test for method moveCustomerStructure.
     *
     * @return void
     */
    public function testMoveCustomerStructure()
    {
        $sourceCustomerId = 1;
        $targetCustomerId = 2;
        $totalCount = 1;
        $testParentNode = '1/2/3';

        $sourceStructure = $this->getMockBuilder(Structure::class)->disableOriginalConstructor()->getMock();
        $targetStructure = $this->getMockBuilder(Structure::class)->disableOriginalConstructor()->getMock();
        $sourceStructure->expects($this->atLeastOnce())->method('getId')->willReturn($sourceCustomerId);
        $targetStructure->expects($this->atLeastOnce())->method('getId')->willReturn($targetCustomerId);

        $this->structureList->expects($this->exactly(4))
            ->method('getItems')
            ->willReturnOnConsecutiveCalls(
                [$sourceStructure],
                [$targetStructure],
                [$sourceStructure],
                [$sourceStructure]
            );
        $this->structureList->expects($this->atLeastOnce())->method('getTotalCount')->willReturn($totalCount);
        $this->structureRepository->expects($this->atLeastOnce())->method('getList')->willReturn($this->structureList);
        $this->getSearchCriteria();

        $sourceStructure->expects($this->atLeastOnce())->method('getData')->with('path')->willReturn($testParentNode);
        $this->prepareTreeForMoveCustomerStructureTest($sourceCustomerId, $targetCustomerId);
        $this->tree->expects($this->exactly(3))->method('getNodeById')
            ->willReturnOnConsecutiveCalls(
                $this->sourceNodeMock,
                $this->targetNodeMock,
                $this->sourceChildNodeMock
            );

        $sourceStructure->expects($this->once())->method('getParentId')->willReturn($sourceCustomerId);
        $this->companyStructure->moveCustomerStructure($sourceCustomerId, $targetCustomerId, false);
    }

    /**
     * Test for method moveCustomerStructure when keepOld parameter is true.
     *
     * @return void
     */
    public function testMoveCustomerStructureKeepOld()
    {
        $sourceCustomerId = 1;
        $targetCustomerId = 2;
        $totalCount = 1;
        $testParentNode = '1/2/3';

        $sourceStructure = $this->getMockBuilder(Structure::class)->disableOriginalConstructor()->getMock();
        $targetStructure = $this->getMockBuilder(Structure::class)->disableOriginalConstructor()->getMock();
        $sourceStructure->expects($this->atLeastOnce())->method('getId')->willReturn($sourceCustomerId);
        $targetStructure->expects($this->atLeastOnce())->method('getId')->willReturn($targetCustomerId);

        $this->structureList->expects($this->exactly(3))
            ->method('getItems')
            ->willReturnOnConsecutiveCalls(
                [$sourceStructure],
                [$targetStructure],
                [$sourceStructure]
            );
        $this->structureList->expects($this->atLeastOnce())->method('getTotalCount')->willReturn($totalCount);
        $this->structureRepository->expects($this->atLeastOnce())->method('getList')->willReturn($this->structureList);
        $this->getSearchCriteria();

        $sourceStructure->expects($this->atLeastOnce())->method('getData')->with('path')->willReturn($testParentNode);
        $this->prepareTreeForMoveCustomerStructureTest($sourceCustomerId, $targetCustomerId);
        $this->tree->expects($this->exactly(4))->method('getNodeById')
            ->willReturnOnConsecutiveCalls(
                false,
                $this->sourceNodeMock,
                $this->targetNodeMock,
                $this->sourceChildNodeMock
            );
        $this->tree->expects($this->atLeastOnce())->method('loadNode')->willReturn($this->sourceNodeMock);
        $this->sourceNodeMock->expects($this->atLeastOnce())->method('loadChildren')->willReturnSelf();

        $dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObject->expects($this->once())->method('getPath')->willReturn($testParentNode);
        $this->structureRepository->expects($this->once())->method('get')->willReturn($dataObject);
        $this->companyStructure->moveCustomerStructure($sourceCustomerId, $targetCustomerId, true);
    }

    /**
     * Prepare tree nodes for testMoveCustomerStructure test.
     *
     * @param int $sourceCustomerId
     * @param int $targetCustomerId
     * @return void
     */
    private function prepareTreeForMoveCustomerStructureTest($sourceCustomerId, $targetCustomerId)
    {
        $this->sourceNodeMock->expects($this->atLeastOnce())->method('hasChildren')->willReturn(true);
        $this->sourceNodeMock->expects($this->atLeastOnce())->method('getId')->willReturn($sourceCustomerId);
        $this->sourceChildNodeMock->expects($this->atLeastOnce())->method('hasChildren')->willReturn(false);
        $this->sourceChildNodeMock->expects($this->atLeastOnce())->method('getChildren')->willReturn([]);
        $this->sourceChildNodeMock->expects($this->atLeastOnce())->method('getData')->willReturn(1);
        $this->sourceChildNodeMock->expects($this->atLeastOnce())->method('getId')->willReturn($sourceCustomerId + 10);
        $this->sourceNodeMock->expects($this->atLeastOnce())->method('getChildren')
            ->willReturn([$this->sourceChildNodeMock]);
        $this->targetNodeMock->expects($this->never())->method('getChildren');
        $this->targetNodeMock->expects($this->atLeastOnce())->method('getId')->willReturn($targetCustomerId);
    }

    /**
     * Test for method moveCustomerStructure.
     *
     * @param int $count
     * @param int $sourceCustomerId
     * @param int $targetCustomerId
     * @param bool $keepOld
     * @return void
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider dataProviderMoveCustomerStructureWithLocalizedException
     */
    public function testMoveCustomerStructureWithLocalizedException(
        $count,
        $sourceCustomerId,
        $targetCustomerId,
        $keepOld
    ) {
        $this->structure->expects($this->exactly($count))
            ->method('getId')
            ->will($this->onConsecutiveCalls($sourceCustomerId, $targetCustomerId));
        $this->getStructureByCustomerId();
        $this->getSearchCriteria();

        $exception = new LocalizedException(__('Could not create team'));
        $this->structureRepository->expects($this->once())->method('save')->willThrowException($exception);
        $this->companyStructure->moveCustomerStructure($sourceCustomerId, $targetCustomerId, $keepOld);
    }

    /**
     * Data provider moveCustomerStructure.
     *
     * @return array
     */
    public function dataProviderMoveCustomerStructureWithLocalizedException()
    {
        return [
            [4, 3, 4, false]
        ];
    }

    /**
     * Test for method filterTree.
     *
     * @return void
     */
    public function testFilterTree()
    {
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $treeMock = $this->getMockBuilder(Tree::class)
            ->disableOriginalConstructor()
            ->setMethods(['removeChild'])
            ->getMockForAbstractClass();
        $nodeMock->expects($this->atLeastOnce())->method('getParent')->willReturn($treeMock);
        $this->companyStructure->filterTree($nodeMock, 'is_active', true);
    }

    /**
     * Test for method getTreeByCustomerId.
     *
     * @return void
     */
    public function testGetTreeByCustomerId()
    {
        $customerId = 17;
        $this->getSearchCriteria();
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);
        $this->companyStructure->getTreeByCustomerId($customerId);
    }

    /**
     * Test for method getAllowedIds.
     *
     * @param int $data
     * @param array $expectedResult
     * @return void
     *
     * @dataProvider testGetAllowedIdsDataProvider
     */
    public function testGetAllowedIds($data, array $expectedResult)
    {
        $userId = 17;
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $nodeMock->expects($this->atLeastOnce())->method('getData')->willReturn($data);
        $nodeMock->expects($this->atLeastOnce())->method('hasChildren')->will($this->onConsecutiveCalls(true));
        $nodeMock->expects($this->atLeastOnce())->method('getChildren')->willReturn([$nodeMock]);
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->getStructureByCustomerId();
        $this->getSearchCriteria();
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);

        $result = $this->companyStructure->getAllowedIds($userId);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * Data Provider for 'testGetAllowedIds' method.
     *
     * @return array
     */
    public function testGetAllowedIdsDataProvider()
    {
        return [
            [
                1,
                [
                    'structures' => [1, 1],
                    'users' => [],
                    'teams' => [1, 1],
                ]
            ],
            [
                2,
                [
                    'structures' => [2, 2],
                    'users' => [2, 2],
                    'teams' => [],
                ]
            ],
        ];
    }

    /**
     * Test for method moveNode.
     *
     * @param int $id
     * @param int $newParentId
     * @return void
     *
     * @dataProvider moveNoteDataProvider
     */
    public function testMoveNode($id, $newParentId)
    {
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $nodeMock->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->onConsecutiveCalls(true, $id));
        $nodeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->onConsecutiveCalls($id, $newParentId, $newParentId, $id));
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->companyStructure->moveNode($id, $newParentId);
    }

    /**
     * Data provider for addNote method.
     *
     * @return array
     */
    public function moveNoteDataProvider()
    {
        return [
            [17, 23, ],
            [17, null, ],
            [null, 23, ],
        ];
    }

    /**
     * Test for method moveNode with exception.
     * Message: 'The company admin cannot be moved to a different location in the company structure.'
     *
     * @param int $id
     * @param int $newParentId
     * @return void
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider moveNoteDataProvider
     */
    public function testMoveNodeWithLocalizedException0($id, $newParentId)
    {
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->companyStructure->moveNode($id, $newParentId);
    }

    /**
     * Test for method moveNode with exception.
     * Message: 'A user or a team cannot be moved under itself.'
     *
     * @param int $id
     * @param int $newParentId
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider moveNoteDataProvider
     */
    public function testMoveNodeWithLocalizedException1($id, $newParentId)
    {
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $nodeMock->expects($this->once())
            ->method('getData')
            ->willReturn(true);
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->companyStructure->moveNode($id, $newParentId);
    }

    /**
     * Test for method moveNode with exception.
     * Message: 'A user or a team cannot be moved under its child user or team.'
     *
     * @param int $id
     * @param int $newParentId
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider moveNoteDataProvider
     */
    public function testMoveNodeWithLocalizedException2($id, $newParentId)
    {
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $nodeMock->expects($this->once())
            ->method('getData')
            ->willReturn(true);
        $nodeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->onConsecutiveCalls($id, $newParentId, $id, $id));
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->companyStructure->moveNode($id, $newParentId);
    }

    /**
     * Test for method moveNode with exception.
     * Message: 'The specified parent ID belongs to a different company.
     *           The specified entity (team or user) and its new parent must belong to the same company.'
     *
     * @param int $id
     * @param int $newParentId
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider moveNoteDataProvider
     */
    public function testMoveNodeWithLocalizedException3($id, $newParentId)
    {
        $nodeMock = $this->getMockBuilder(Node::class)->disableOriginalConstructor()->getMock();
        $nodeMock->expects($this->atLeastOnce())
            ->method('getData')
            ->will($this->onConsecutiveCalls(true, $id));
        $nodeMock->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->onConsecutiveCalls($id, $newParentId, $newParentId, $id, $id, $newParentId));
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->companyStructure->moveNode($id, $newParentId);
    }

    /**
     * Test for method getTreeById.
     *
     * @return void
     */
    public function testGetTreeById()
    {
        $id = 14;
        $nodeMock = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($nodeMock);
        $this->companyStructure->getTreeById($id);
    }

    /**
     * Test for method getStructureByCustomerId.
     *
     * @return void
     */
    public function testGetStructureByCustomerId()
    {
        $id = 24;
        $this->getSearchCriteria();
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);
        $this->companyStructure->getStructureByCustomerId($id);
    }

    /**
     * Test for method getStructureByTeamId.
     *
     * @return void
     */
    public function testGetStructureByTeamId()
    {
        $id = 24;
        $this->getSearchCriteria();
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);
        $this->companyStructure->getStructureByTeamId($id);
    }

    /**
     * Test for method addNode.
     *
     * @param int $entityId
     * @param int $entityType
     * @param int $parentId
     * @param string $testParentNode
     *
     * @return void
     * @dataProvider addNoteDataProvider
     */
    public function testAddNode($entityId, $entityType, $parentId, $testParentNode)
    {
        $dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObject->expects($this->atLeastOnce())->method('getPath')->willReturn($testParentNode);
        $this->structureRepository->expects($this->atLeastOnce())->method('get')->willReturn($dataObject);
        $this->structureFactory->expects($this->atLeastOnce())->method('create')->willReturn($this->structure);
        $this->companyStructure->addNode($entityId, $entityType, $parentId);
    }

    /**
     * Data provider for addNote method.
     *
     * @return array
     */
    public function addNoteDataProvider()
    {
        return [
            [1, 1, 21, '1/2/3'],
        ];
    }

    /**
     * Test for 'removeCustomerNode' method.
     *
     * @return void
     */
    public function testRemoveCustomerNode()
    {
        $customerId = 32;
        $this->getSearchCriteria();
        $this->getStructureByCustomerId();
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);
        $this->companyStructure->removeCustomerNode($customerId);
    }

    /**
     * Test for 'removeCustomerNode' method with 'LocalizedException'.
     *
     * @return void
     */
    public function testRemoveCustomerNodeWithLocalizedException()
    {
        $customerId = 32;
        $this->getSearchCriteria();
        $this->getStructureByCustomerId();
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);

        $exception = new LocalizedException(__('Error message'));
        $this->structureRepository->expects($this->once())
            ->method('delete')
            ->willThrowException($exception);
        $this->companyStructure->removeCustomerNode($customerId);
    }

    /**
     * Test for method moveStructureChildrenToParent.
     *
     * @param array $array
     * @param string $field
     *
     * @dataProvider addDataToTreeDataProvider
     *
     * @return void
     */
    public function testMoveStructureChildrenToParent(array $array, $field)
    {
        $id = 17;
        $newParentId = 23;
        $customerId = 5;

        $tree = $this->objectManager->getObject(Tree::class);
        $treeNode = $this->getMockBuilder(Node::class)
            ->setConstructorArgs([
                'data' => $array,
                'idField' => $field,
                'tree' => $tree
            ])
            ->getMock();

        $treeNode->expects($this->atLeastOnce())->method('getData')->willReturn(true);
        $treeNode->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->onConsecutiveCalls($id, $newParentId, $newParentId, $id));

        $dataObject = $this->objectManager->getObject(DataObject::class, ['data' => [$treeNode]]);
        $treeNode->expects($this->once())->method('getChildren')->willReturn([$dataObject]);
        $this->tree->expects($this->atLeastOnce())->method('getNodeById')->willReturn($treeNode);
        $this->getStructureByCustomerId();
        $this->getSearchCriteria();
        $this->companyStructure->moveStructureChildrenToParent($customerId);
    }

    /**
     * Test for 'getTeamNameByCustomerId' method.
     *
     * @param int $entityId
     * @param int $counter
     * @param int $getParentIdCounter
     * @param int $callCounter
     * @param string $expectedResult
     * @return void
     *
     * @dataProvider getTeamNameByCustomerIdDataProvider
     */
    public function testGetTeamNameByCustomerId($entityId, $counter, $getParentIdCounter, $callCounter, $expectedResult)
    {
        $team = $this->getMockBuilder(TeamInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->getStructureByCustomerId();
        $this->getSearchCriteria();
        $this->structure->expects($this->exactly($counter))
            ->method('getEntityType')
            ->willReturnOnConsecutiveCalls($entityId, 1);
        $this->structure->expects($this->exactly($callCounter))->method('getEntityId')->willReturn(1);
        $this->structure->expects($this->exactly($getParentIdCounter))
            ->method('getParentId')
            ->willReturn(2);
        $this->structureRepository->expects($this->exactly($getParentIdCounter))
            ->method('get')
            ->with(2)
            ->willReturn($this->structure);
        $this->teamRepositoryInterface->expects($this->exactly($callCounter))
            ->method('get')
            ->with(1)
            ->willReturn($team);
        $team->expects($this->exactly($callCounter))->method('getName')->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->companyStructure->getTeamNameByCustomerId(1));
    }

    /**
     * Data provider for 'testGetTeamNameByCustomerId' method.
     *
     * @return array
     */
    public function getTeamNameByCustomerIdDataProvider()
    {
        return [
            [1, 1, 0, 1, 'Team Name'],
            [0, 2, 1, 1, 'Team Name'],
            [2, 1, 0, 0, ''],
        ];
    }

    /**
     * Unit test for 'getUserChildTeams' method.
     *
     * @return void
     */
    public function testGetUserChildTeams()
    {
        $userId = 1;
        $this->getStructureByCustomerId();
        $this->getSearchCriteria();
        $this->assertEquals([$this->structure], $this->companyStructure->getUserChildTeams($userId));
    }

    /**
     * Function to build Search Criterias.
     *
     * @return void
     */
    private function getSearchCriteria()
    {
        $group = $this->getMockBuilder(FilterGroup::class)->disableOriginalConstructor()->getMock();
        $filter = $this->getMockBuilder(Filter::class)->disableOriginalConstructor()->getMock();
        $group->setFilters([$filter]);
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)->disableOriginalConstructor()->getMock();
        $searchCriteria->setFilterGroups([$group]);
        $sort = $this->getMockBuilder(SortOrder::class)->disableOriginalConstructor()->getMock();
        $searchCriteria->setSortOrders([$sort]);
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilter')->willReturnSelf();
    }

    /**
     * Methods of the tested class are hungry for the 'getStructureByCustomerId' method.
     * And this is an initialization of it's functionality.
     *
     * @return void
     */
    private function getStructureByCustomerId()
    {
        $totalCount = 1;
        $this->structureList->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$this->structure]);
        $this->structureList->expects($this->atLeastOnce())->method('getTotalCount')->willReturn($totalCount);
        $this->structureRepository->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($this->structureList);
    }
}
