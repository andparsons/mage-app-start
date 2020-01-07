<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Unit test for StructureRepository model.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StructureRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureFactory;

    /**
     * @var \Magento\Company\Model\ResourceModel\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureResource;

    /**
     * @var \Magento\Company\Model\Structure\SearchProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchProvider;

    /**
     * @var \Magento\Company\Model\StructureRepository
     */
    private $structureRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->structureFactory = $this->getMockBuilder(\Magento\Company\Model\StructureFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->structureResource = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchProvider = $this
            ->getMockBuilder(\Magento\Company\Model\Structure\SearchProvider::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->structureRepository = $objectManager->getObject(
            \Magento\Company\Model\StructureRepository::class,
            [
                'structureFactory' => $this->structureFactory,
                'structureResource' => $this->structureResource,
                'searchProvider' => $this->searchProvider,
            ]
        );
    }

    /**
     * Test for save method.
     *
     * @return void
     */
    public function testSave()
    {
        $structureId = 1;
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $this->structureResource->expects($this->once())->method('save')->with($structure)->willReturnSelf();
        $structure->expects($this->atLeastOnce())->method('getId')->willReturn($structureId);
        $this->assertEquals($structureId, $this->structureRepository->save($structure));
    }

    /**
     * Test for save method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save company
     */
    public function testSaveWithException()
    {
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $this->structureResource->expects($this->once())
            ->method('save')->with($structure)->willThrowException(new \Exception());
        $this->structureRepository->save($structure);
    }

    /**
     * Test for get method.
     *
     * @return void
     */
    public function testGet()
    {
        $structureId = 1;
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $this->structureFactory->expects($this->once())->method('create')->willReturn($structure);
        $structure->expects($this->once())->method('load')->with($structureId)->willReturnSelf();
        $structure->expects($this->atLeastOnce())->method('getId')->willReturn($structureId);
        $this->assertEquals($structure, $this->structureRepository->get($structureId));
    }

    /**
     * Test for get method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetWithException()
    {
        $structureId = 1;
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $this->structureFactory->expects($this->once())->method('create')->willReturn($structure);
        $structure->expects($this->once())->method('load')->with($structureId)->willReturnSelf();
        $structure->expects($this->atLeastOnce())->method('getId')->willReturn(null);
        $this->structureRepository->get($structureId);
    }

    /**
     * Test for delete method.
     *
     * @return void
     */
    public function testDelete()
    {
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $structure->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->structureResource->expects($this->once())->method('delete')->with($structure)->willReturnSelf();
        $this->assertTrue($this->structureRepository->delete($structure));
    }

    /**
     * Test for delete method with exception.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete structure with id 1
     */
    public function testDeleteWithException()
    {
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $structure->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->structureResource->expects($this->once())
            ->method('delete')->with($structure)->willThrowException(new \Exception());
        $this->structureRepository->delete($structure);
    }

    /**
     * Test for deleteById method.
     *
     * @return void
     */
    public function testDeleteById()
    {
        $structureId = 1;
        $structure = $this->getMockBuilder(\Magento\Company\Model\Structure::class)
            ->disableOriginalConstructor()->getMock();
        $structure->expects($this->atLeastOnce())->method('getId')->willReturn($structureId);
        $this->structureFactory->expects($this->once())->method('create')->willReturn($structure);
        $structure->expects($this->once())->method('load')->with($structureId)->willReturnSelf();
        $this->structureResource->expects($this->once())->method('delete')->with($structure)->willReturnSelf();
        $this->assertTrue($this->structureRepository->deleteById($structureId));
    }

    /**
     * Test for getList method.
     *
     * @return void
     */
    public function testGetList()
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $searchResults = $this->getMockBuilder(\Magento\Company\Api\Data\StructureSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->searchProvider->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);

        $this->assertEquals($searchResults, $this->structureRepository->getList($searchCriteria));
    }
}
