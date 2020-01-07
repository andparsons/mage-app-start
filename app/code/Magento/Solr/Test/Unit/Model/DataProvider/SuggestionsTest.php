<?php

namespace Magento\Solr\Test\Unit\Model\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuggestionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Search\Request\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestBuilder;

    /**
     * @var \Magento\Framework\Search\SearchEngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchEngine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Solr\Helper\Data
     */
    private $helperData;

    /**
     * @var \Magento\Solr\Model\DataProvider\Suggestions
     */
    private $dataProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Search\Model\QueryResultFactory
     */
    private $queryResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Solr\SearchAdapter\ConnectionManager
     */
    private $connectionManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Solr\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Solr\SearchAdapter\AccessPointMapperInterface
     */
    private $accessPointMapper;

    protected function setUp()
    {
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->helperData = $this->getMockBuilder(\Magento\Solr\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryResultFactory = $this->getMockBuilder(\Magento\Search\Model\QueryResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionManager = $this->getMockBuilder(\Magento\Solr\SearchAdapter\ConnectionManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryFactory = $this->getMockBuilder(\Magento\Solr\Model\QueryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->accessPointMapper = $this->getMockBuilder(\Magento\Solr\SearchAdapter\AccessPointMapperInterface::class)
            ->getMockForAbstractClass();
        $this->requestBuilder = $this->getMockBuilder(\Magento\Framework\Search\Request\Builder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestBuilder->expects($this->any())
            ->method('setRequestName')
            ->with('quick_search_container')
            ->willReturnSelf();
        $this->searchEngine = $this->getMockBuilder(\Magento\Framework\Search\SearchEngineInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\Solr\Model\DataProvider\Suggestions::class,
            [
                'scopeConfig' => $this->scopeConfig,
                'searchData' => $this->helperData,
                'queryResultFactory' => $this->queryResultFactory,
                'connectionManager' => $this->connectionManager,
                'queryFactory' => $this->queryFactory,
                'accessPointMapper' => $this->accessPointMapper,
                'requestBuilder' => $this->requestBuilder,
                'searchEngine' => $this->searchEngine,
            ]
        );
    }

    /**
     * @param $configValue
     * @param bool $expectedResult
     * @dataProvider isCountResultsEnabledDataProvider
     */
    public function testIsCountResultsEnabled($configValue, $expectedResult)
    {
        $this->setIsCountResultEnabled($configValue);
        $this->assertEquals($expectedResult, $this->dataProvider->isResultsCountEnabled());
    }

    private function setIsCountResultEnabled($isEnabled, $sequence = 0)
    {
        $this->scopeConfig->expects($this->at($sequence))
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\Solr\Model\DataProvider\Suggestions::CONFIG_SUGGESTION_COUNT_RESULTS_ENABLED),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            )->willReturn($isEnabled);
    }

    /**
     * @return array
     */
    public function isCountResultsEnabledDataProvider()
    {
        return [
            ['0', false],
            [0, false],
            [false, false],
            ['false', true],
            [true, true],
            [1, true],
            [100500, true],
        ];
    }

    public function testGetItems()
    {
        $expectedResult = [
            ['queryText' => 'Query Text 1', 'resultsCount' => 543],
            ['queryText' => 'Query Text 2', 'resultsCount' => 788],
        ];
        $searchQueryText = 'Search Query Text';

        $this->setIsSuggestionsEnabled(true, 0);
        $this->setIsCountResultEnabled(true, 1);

        $spellCheck = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Component\Spellcheck::class)
            ->setMethods(['setQuery', 'setCount', 'setExtendedResults'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $solariumSelect */
        $solariumSelect = $this->getMockBuilder(\Solarium\QueryType\Select\Query\Query::class)
            ->setMethods(['setHandler', 'setRows', 'getSpellcheck'])
            ->disableOriginalConstructor()
            ->getMock();
        $solariumSelect->expects($this->once())
            ->method('getSpellcheck')
            ->willReturn($spellCheck);
        $this->queryFactory->expects($this->once())
            ->method('create')
            ->willReturn($solariumSelect);

        $this->accessPointMapper->expects($this->once())
            ->method('getHandler')
            ->willReturn('/testHandler');
        $solariumSelect->expects($this->once())
            ->method('setHandler')
            ->with($this->equalTo('/testHandler'))
            ->willReturnSelf();
        $solariumSelect->expects($this->once())
            ->method('setRows')
            ->with($this->equalTo(0))
            ->willReturnSelf();

        $searchQuery = $this->getMockBuilder(\Magento\Search\Model\QueryInterface::class)
            ->getMockForAbstractClass();
        $searchQuery->expects($this->once())
            ->method('getQueryText')
            ->willReturn($searchQueryText);

        $spellCheck->expects($this->once())
            ->method('setQuery')
            ->with($this->equalTo($searchQueryText))
            ->willReturnSelf();
        $this->scopeConfig->expects($this->at(2))
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\Solr\Model\DataProvider\Suggestions::CONFIG_SUGGESTION_COUNT),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            )->willReturn(120);
        $spellCheck->expects($this->once())
            ->method('setCount')
            ->with($this->equalTo(120))
            ->willReturnSelf();
        $spellCheckResult = $this->getSpellcheckResultMock();

        $spellCheckItems = [
            $this->createSpellCheck($expectedResult[0]['queryText']),
            $this->createSpellCheck($expectedResult[1]['queryText']),
        ];

        $this->mockSuggestionCountRequest(0, $expectedResult[0]['queryText']);
        $this->mockSuggestionCountResponse(0, $expectedResult[0]['resultsCount']);
        $this->mockSuggestionCountRequest(3, $expectedResult[1]['queryText']);
        $this->mockSuggestionCountResponse(1, $expectedResult[1]['resultsCount']);

        $spellCheckResult->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($spellCheckItems));

        $this->queryResultFactory->expects($this->any())
            ->method('create')
            ->willReturnArgument(0);

        $this->assertEquals($expectedResult, $this->dataProvider->getItems($searchQuery));
    }

    private function setIsSuggestionsEnabled($isEnabled, $sequence = 0)
    {
        $this->scopeConfig->expects($this->at($sequence))
            ->method('getValue')
            ->with(
                $this->equalTo(\Magento\Solr\Model\DataProvider\Suggestions::CONFIG_SUGGESTION_ENABLED),
                $this->equalTo(\Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            )->willReturn($isEnabled);
        $this->helperData->expects($this->once())
            ->method('isSolrEnabled')
            ->willReturn(true);
        $this->helperData->expects($this->once())
            ->method('isActiveEngine')
            ->willReturn(true);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getSpellcheckResultMock()
    {
        $client = $this->getMockBuilder(\Magento\Solr\Model\Client\Solarium::class)
            ->setMethods(['query'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionManager->expects($this->once())
            ->method('getConnection')
            ->willReturn($client);
        $selectResult = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Result::class)
            ->setMethods(['getSpellcheck'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $client->expects($this->once())
            ->method('query')
            ->willReturn($selectResult);
        $spellCheckResult = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Spellcheck\Result::class)
            ->setMethods(['getIterator'])
            ->disableOriginalConstructor()
            ->getMock();
        $selectResult->expects($this->once())
            ->method('getSpellcheck')
            ->willReturn($spellCheckResult);
        return $spellCheckResult;
    }

    private function createSpellCheck($word)
    {
        $result = $this->getMockBuilder(\Solarium\QueryType\Select\Result\Spellcheck\Suggestion::class)
            ->setMethods(['getWord'])
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->any())->method('getWord')->willReturn($word);
        return $result;
    }

    private function mockSuggestionCountRequest($sequence, $word)
    {
        $sequence++;
        $this->requestBuilder->expects($this->at($sequence))
            ->method('bind')
            ->with('search_term', $word)
            ->willReturnSelf();
        $request = $this->getMockBuilder(\Magento\Framework\Search\Request::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestBuilder->expects($this->at($sequence + 1))
            ->method('create')
            ->willReturn($request);
    }

    /**
     * @param $sequence
     * @param $count
     */
    private function mockSuggestionCountResponse($sequence, $count)
    {
        $response = $this->getMockBuilder(\Magento\Framework\Search\Response\QueryResponse::class)
            ->setMethods(['count'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $response->expects($this->once())->method('count')->willReturn($count);
        $this->searchEngine->expects($this->at($sequence))->method('search')->willReturn($response);
    }
}
