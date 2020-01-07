<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for Magento\Company\Model\CompanyHierarchy class.
 */
class CompanyHierarchyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\HierarchyInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $hierarchyFactory;

    /**
     * @var \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structure;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Model\CompanyHierarchy
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->hierarchyFactory = $this->getMockBuilder(\Magento\Company\Api\Data\HierarchyInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->structure = $this->getMockBuilder(\Magento\Company\Model\Company\Structure::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->companyRepository = $this->getMockBuilder(\Magento\Company\Api\CompanyRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\CompanyHierarchy::class,
            [
                'hierarchyFactory' => $this->hierarchyFactory,
                'structure' => $this->structure,
                'companyRepository' => $this->companyRepository,
            ]
        );
    }

    /**
     * Test moveNode method.
     *
     * @return void
     */
    public function testMoveNode()
    {
        $id = 2;
        $newParentId = 5;
        $this->structure->expects($this->once())->method('moveNode')->with($id, $newParentId);

        $this->model->moveNode($id, $newParentId);
    }

    /**
     * Test getCompanyHierarchy method.
     *
     * @param string $structureType
     * @param string $hierarchyType
     * @return void
     * @dataProvider getCompanyHierarchyDataProvider
     */
    public function testGetCompanyHierarchy($structureType, $hierarchyType)
    {
        $id = 2;
        $superUserId = 3;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $tree = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node::class)
            ->disableOriginalConstructor()
            ->getMock();
        $treeCollection = $this->getMockBuilder(\Magento\Framework\Data\Tree\Node\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $hierarchy = $this->getMockBuilder(\Magento\Company\Api\Data\HierarchyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository->expects($this->once())->method('get')->with($id)->willReturn($company);
        $company->expects($this->once())->method('getSuperUserId')->willReturn($superUserId);
        $this->structure->expects($this->once())
            ->method('getTreeByCustomerId')
            ->with($superUserId)
            ->willReturn($tree);
        $tree->expects($this->atLeastOnce())->method('hasChildren')->willReturnOnConsecutiveCalls(true, false);
        $tree->expects($this->once())->method('getChildren')->willReturn(new \ArrayIterator($treeCollection));
        $tree->expects($this->atLeastOnce())
            ->method('getData')
            ->withConsecutive(['structure_id'], ['parent_id'], ['entity_id'], ['entity_type'])
            ->willReturnOnConsecutiveCalls(4, 3, 5, $structureType);
        $this->hierarchyFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => [
                        'structure_id' => 4,
                        'structure_parent_id' => 3,
                        'entity_id' => 5,
                        'entity_type' => $hierarchyType
                    ]
                ]
            )
            ->willReturn($hierarchy);

        $this->assertSame([$hierarchy], $this->model->getCompanyHierarchy($id));
    }

    /**
     * Data provider for getCompanyHierarchy method.
     *
     * @return array
     */
    public function getCompanyHierarchyDataProvider()
    {
        return [
            [
                \Magento\Company\Api\Data\StructureInterface::TYPE_CUSTOMER,
                \Magento\Company\Api\Data\HierarchyInterface::TYPE_CUSTOMER
            ],
            [
                \Magento\Company\Api\Data\StructureInterface::TYPE_TEAM,
                \Magento\Company\Api\Data\HierarchyInterface::TYPE_TEAM
            ],
        ];
    }

    /**
     * Test getCompanyHierarchy method with empty tree.
     *
     * @return void
     */
    public function testGetCompanyHierarchyWithEmptyTree()
    {
        $id = 2;
        $superUserId = 3;
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyRepository->expects($this->once())->method('get')->with($id)->willReturn($company);
        $company->expects($this->once())->method('getSuperUserId')->willReturn($superUserId);
        $this->structure->expects($this->once())
            ->method('getTreeByCustomerId')
            ->with($superUserId)
            ->willReturn(null);

        $this->assertEquals([], $this->model->getCompanyHierarchy($id));
    }
}
