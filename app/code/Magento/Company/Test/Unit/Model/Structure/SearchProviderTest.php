<?php

namespace Magento\Company\Test\Unit\Model\Structure;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Company\Model\ResourceModel\Structure\CollectionFactory;

/**
 * Test for \Magento\Company\Model\Structure\SearchProvider class
 */
class SearchProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Api\SearchResultsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var \Magento\Company\Model\Structure\SearchProvider
     */
    private $searchProvider;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $companyStructure = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Structure::class)
            ->setMethods(['getId', 'load', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();
        $companyStructure->expects($this->any())->method('load')->will($this->returnSelf());
        $companyStructure->expects($this->any())->method('getId')->willReturn(14);
        $companyStructure->expects($this->any())->method('delete')->will($this->returnSelf());

        $this->searchResultsFactory = $this->getMockBuilder(\Magento\Framework\Api\SearchResultsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $searchResult = new \Magento\Framework\Api\SearchResults();
        $this->searchResultsFactory->expects($this->any())->method('create')->will($this->returnValue($searchResult));

        $this->collectionFactory =
            $this->getMockBuilder(CollectionFactory::class)
                ->disableOriginalConstructor()
                ->setMethods(['create'])
                ->getMock();

        $this->collectionProcessorMock = $this->getMockBuilder(CollectionProcessorInterface::class)
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchProvider = $objectManager->getObject(
            \Magento\Company\Model\Structure\SearchProvider::class,
            [
                'searchResultsFactory' => $this->searchResultsFactory,
                'collectionFactory'    => $this->collectionFactory,
                'collectionProcessor'  => $this->collectionProcessorMock,
            ]
        );
    }

    /**
     * Test for method \Magento\Company\Model\Structure\SearchProvider::getList
     * @dataProvider getParamsForModel
     *
     * @param $count
     * @param $expectedResult
     * @return void
     */
    public function testGetList($count, $expectedResult)
    {
        $searchCriteria = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\Company\Model\ResourceModel\Structure\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->any())
            ->method('create')->will($this->returnValue($collection));
        $collection->expects($this->any())->method('getItems')->will($this->returnValue([]));
        $collection->expects($this->any())->method('getSize')->will($this->returnValue($count));

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteria, $collection);

        $result = $this->searchProvider->getList($searchCriteria);
        $this->assertEquals($expectedResult, $result->getTotalCount());
    }

    /**
     * Data provider for method testGetList
     *
     * @return array
     */
    public function getParamsForModel()
    {
        return [
            [0, 0],
            [1, 1]
        ];
    }
}
