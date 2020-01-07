<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\DataProvider;

/**
 * Class DataProviderTest
 * @package Magento\NegotiableQuote\Test\Unit\Ui\DataProvider
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Ui\DataProvider\DataProvider
     */
    private $quoteDataProvider;

    /**
     * @var  \Magento\NegotiableQuote\Model\NegotiableQuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var  \Magento\Company\Model\Company\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    private $structureMock;

    /**
     * @var \Magento\Framework\Api\SearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResult;

    /**
     * setUp
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $className = \Magento\NegotiableQuote\Ui\DataProvider\DataProvider::class;
        $arguments = $this->objectManagerHelper->getConstructArguments($className);

        $searchCriteriaBuilderMock = $arguments['searchCriteriaBuilder'];
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\Search\SearchCriteria::class);
        $searchCriteriaBuilderMock->expects($this->any())->method('create')->willReturn($searchCriteriaMock);

        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $website = $this->createMock(\Magento\Store\Model\Website::class);
        $storeManager->expects($this->any())->method('getStore')->willReturn($storeManager);
        $website->expects($this->any())->method('getStoreIds')->willReturn(1);
        $storeManager->expects($this->any())->method('getWebsite')->willReturn($website);
        $arguments['storeManager'] = $storeManager;

        $filterBuilder = $arguments['filterBuilder'];
        $filter = $this->getMockForAbstractClass(
            \Magento\Framework\Api\Filter::class,
            [],
            '',
            false
        );
        $filterBuilder->expects($this->any())->method('setField')->will($this->returnSelf());
        $filterBuilder->expects($this->any())->method('setConditionType')->will($this->returnSelf());
        $filterBuilder->expects($this->any())->method('setValue')->will($this->returnSelf());
        $filterBuilder->expects($this->any())->method('create')->willReturn($filter);

        $this->searchResult = $this->createMock(\Magento\Framework\Api\SearchResultsInterface::class);
        $this->negotiableQuoteRepository = $arguments['negotiableQuoteRepository'];
        $this->negotiableQuoteRepository->expects($this->once())->method('getList')->willReturn($this->searchResult);
        $this->structureMock = $arguments['structure'];
        $this->structureMock->expects($this->any())
            ->method('getAllowedChildrenIds')
            ->willReturn([
                1,
                2
            ]);

        $this->quoteDataProvider = $this->objectManagerHelper->getObject($className, $arguments);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @param array $items
     * @param array $attributes
     */
    public function testGetData($items, $attributes)
    {
        $this->searchResult->expects($this->any())->method('getItems')->willReturn($items);
        $data = $this->quoteDataProvider->getData();

        foreach ($data['items'] as $item) {
            foreach (array_keys($attributes) as $key) {
                $this->assertArrayHasKey($key, $item);
            }
        }
    }

    /**
     * data provider for getData
     * @return array
     */
    public function getDataProvider()
    {
        return [
            [$this->getItems(), ['quote_name' => 'name_1']]
        ];
    }

    /**
     * @return array
     */
    protected function getItems()
    {
        return [
            $this->initItem(['key' => 'value'], ['quote_name' => 'name_1']),
            $this->initItem(['key' => 'value'], ['quote_name' => 'name_1']),
            $this->initItem(['key' => 'value'], ['quote_name' => 'name_1']),
            $this->initItem(['key' => 'value'], ['quote_name' => 'name_1'])
        ];
    }

    /**
     * @param $data
     * @param $quoteData
     * @return object
     */
    protected function initItem($data, $quoteData)
    {
        $itemClassName = \Magento\Quote\Model\Quote::class;
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $itemArguments = $objectManagerHelper->getConstructArguments($itemClassName);
        $itemArguments['data'] = $data;
        $item = $this->createPartialMock($itemClassName, ['getExtensionAttributes']);

        $quoteNegotiation =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\NegotiableQuote::class, ['getData']);
        $quoteNegotiation->expects($this->any())->method('getData')->willReturn($quoteData);

        $extensionMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartExtensionInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getNegotiableQuote']
        );
        $extensionMock->expects($this->any())->method('getNegotiableQuote')->willReturn($quoteNegotiation);

        $item->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionMock);

        return $item;
    }
}
