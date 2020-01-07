<?php

namespace Magento\Company\Test\Unit\Model\Company;

/**
 * Unit test for Magento\Company\Model\Company\GetList class.
 */
class GetListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Model\ResourceModel\Company\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyCollectionFactory;

    /**
     * @var \Magento\Company\Api\Data\CompanySearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    /**
     * @var \Magento\Company\Model\Company\GetList
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->companyCollectionFactory = $this->getMockBuilder(
            \Magento\Company\Model\ResourceModel\Company\CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->searchResultsFactory = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanySearchResultsInterfaceFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->extensionAttributesJoinProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collectionProcessor = $this->getMockBuilder(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Company\Model\Company\GetList::class,
            [
                'companyCollectionFactory' => $this->companyCollectionFactory,
                'searchResultsFactory' => $this->searchResultsFactory,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessor,
                'collectionProcessor' => $this->collectionProcessor,
            ]
        );
    }

    /**
     * Test getList method.
     *
     * @return void
     */
    public function testGetList()
    {
        $searchResults = $this->getMockBuilder(
            \Magento\Company\Api\Data\CompanySearchResultsInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Company\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $item = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchResultsFactory->expects($this->once())->method('create')->willReturn($searchResults);
        $searchResults->expects($this->once())->method('setSearchCriteria')->with($searchCriteria)->willReturnSelf();
        $this->companyCollectionFactory->expects($this->once())->method('create')->willReturn($collection);
        $this->extensionAttributesJoinProcessor->expects($this->once())->method('process')->with($collection);
        $this->collectionProcessor->expects($this->once())->method('process')->with($searchCriteria, $collection);
        $collection->expects($this->once())->method('getSize')->willReturn(1);
        $collection->expects($this->once())->method('getItems')->willReturn([$item]);
        $searchResults->expects($this->once())->method('setTotalCount')->with(1)->willReturnSelf();
        $searchResults->expects($this->once())->method('setItems')->with([$item])->willReturnSelf();

        $this->assertSame($searchResults, $this->model->getList($searchCriteria));
    }
}
