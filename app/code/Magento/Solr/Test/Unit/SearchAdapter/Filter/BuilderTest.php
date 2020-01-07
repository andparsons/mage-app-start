<?php

namespace Magento\Solr\Test\Unit\SearchAdapter\Filter;

use Magento\Framework\DB\Select;
use Magento\Solr\SearchAdapter\ConditionManager;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression as RequestBoolQuery;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Solr\SearchAdapter\Filter\Builder
     */
    private $builder;

    /**
     * @var ConditionManager|\PHPUnit_Framework_MockObject_MockObject $conditionManager
     */
    private $conditionManager;

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->markTestSkipped('Temporary');
        $objectManager = new ObjectManager($this);

        $this->conditionManager = $this->getMockBuilder(\Magento\Solr\SearchAdapter\ConditionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateCondition', 'combineQueries', 'wrapBrackets'])
            ->getMock();
        $this->conditionManager->expects($this->any())
            ->method('generateCondition')
            ->will(
                $this->returnCallback(
                    function ($field, $operator, $value) {
                        return sprintf('%s %s %s', $field, $operator, $value);
                    }
                )
            );
        $this->conditionManager->expects($this->any())
            ->method('combineQueries')
            ->will(
                $this->returnCallback(
                    function ($queries, $operator) {
                        return implode(
                            ' ' . $operator . ' ',
                            array_filter($queries, 'strlen')
                        );
                    }
                )
            );
        $this->conditionManager->expects($this->any())
            ->method('wrapBrackets')
            ->will(
                $this->returnCallback(
                    function ($query) {
                        return !empty($query) ? sprintf('(%s)', $query) : '';
                    }
                )
            );

        $rangeBuilder = $this->getMockBuilder(\Magento\Solr\SearchAdapter\Filter\Builder\Range::class)
            ->setMethods(['buildFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $rangeBuilder->expects($this->any())
            ->method('buildFilter')
            ->will(
                $this->returnCallback(
                    function (FilterInterface $filter, $isNegation) {
                        /**
                         * @var \Magento\Framework\Search\Request\Filter\Range $filter
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter
                         */
                        $fromCondition = '';
                        if ($filter->getFrom() !== null) {
                            $fromCondition = $this->conditionManager->generateCondition(
                                $filter->getField(),
                                ($isNegation ? '<' : '>='),
                                $filter->getFrom()
                            );
                        }
                        $toCondition = '';
                        if ($filter->getTo() !== null) {
                            $toCondition = $this->conditionManager->generateCondition(
                                $filter->getField(),
                                ($isNegation ? '>=' : '<'),
                                $filter->getTo()
                            );
                        }
                        $unionOperator = $isNegation ? Select::SQL_OR : Select::SQL_AND;

                        return $this->conditionManager->combineQueries([$fromCondition, $toCondition], $unionOperator);
                    }
                )
            );

        $termBuilder = $this->getMockBuilder(\Magento\Solr\SearchAdapter\Filter\Builder\Term::class)
            ->setMethods(['buildFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $termBuilder->expects($this->any())
            ->method('buildFilter')
            ->will(
                $this->returnCallback(
                    function (FilterInterface $filter, $isNegation) {
                        /**
                         * @var \Magento\Framework\Search\Request\Filter\Term $filter
                         * @var \Magento\Framework\DB\Adapter\AdapterInterface $adapter
                         */
                        return $this->conditionManager->generateCondition(
                            $filter->getField(),
                            ($isNegation ? '!=' : '='),
                            $filter->getValue()
                        );
                    }
                )
            );
        $this->builder = $objectManager->getObject(
            \Magento\Solr\SearchAdapter\Filter\Builder::class,
            [
                'range' => $rangeBuilder,
                'term' => $termBuilder,
                'conditionManager' => $this->conditionManager
            ]
        );
    }

    /**
     * @param FilterInterface|\PHPUnit_Framework_MockObject_MockObject $filter
     * @param string $conditionType
     * @param string $expectedResult
     * @dataProvider buildFilterDataProvider
     */
    public function testBuildFilter($filter, $conditionType, $expectedResult)
    {
        $actualResult = $this->builder->build($filter, $conditionType);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function buildFilterDataProvider()
    {
        return array_merge(
            $this->buildTermFilterDataProvider(),
            $this->buildRangeFilterDataProvider()
        );
    }

    /**
     * @return array
     */
    public function buildTermFilterDataProvider()
    {
        return [
            'termFilter' => [
                'filter' => $this->createTermFilter('term1', 123),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(term1 = 123)',
            ],
            'termFilterNegative' => [
                'filter' => $this->createTermFilter('term1', 123),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_NOT,
                'expectedResult' => '(term1 != 123)',
            ],
        ];
    }

    public function testBuildBoolFilter()
    {
        $filter = $this->createBoolFilter(
            [
                $this->createTermFilter('fieldTerm1', 'valueTerm1'),
                $this->createRangeFilter('fieldRange1', 'valueRangeFrom1', 'valueRangeTo1'),
            ],
            [
                $this->createTermFilter('fieldTerm2', 'valueTerm2'),
                $this->createRangeFilter('fieldRange2', 'valueRangeFrom2', 'valueRangeTo2'),
            ],
            [
                $this->createTermFilter('fieldTerm3', 'valueTerm3'),
                $this->createRangeFilter('fieldRange3', 'valueRangeFrom3', 'valueRangeTo3'),
            ]
        );
        $expectedResult = '(((fieldTerm1 = valueTerm1)'
            .' AND (fieldRange1 >= valueRangeFrom1 AND fieldRange1 < valueRangeTo1)'
            . ' AND ((fieldTerm2 = valueTerm2) OR (fieldRange2 >= valueRangeFrom2 AND fieldRange2 < valueRangeTo2))'
            . ' AND ((fieldTerm3 != valueTerm3) AND (fieldRange3 < valueRangeFrom3 OR fieldRange3 >= valueRangeTo3))))';
        $actualResult = $this->builder->build($filter, RequestBoolQuery::QUERY_CONDITION_MUST);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @param array $must
     * @param array $should
     * @param array $mustNot
     * @return \Magento\Framework\Search\Request\Filter\BoolExpression|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createBoolFilter(array $must = [], array $should = [], array $mustNot = [])
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\BoolExpression::class)
            ->setMethods(['getMust', 'getShould', 'getMustNot'])
            ->disableOriginalConstructor()
            ->getMock();
        $filter->expects($this->once())
            ->method('getMust')
            ->willReturn($must);
        $filter->expects($this->once())
            ->method('getShould')
            ->willReturn($should);
        $filter->expects($this->once())
            ->method('getMustNot')
            ->willReturn($mustNot);
        return $filter;
    }

    /**
     * @param $field
     * @param $value
     * @return \Magento\Framework\Search\Request\Filter\Term|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTermFilter($field, $value)
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
            ->setMethods(['getField', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $filter->expects($this->exactly(1))
            ->method('getField')
            ->will($this->returnValue($field));
        $filter->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));
        return $filter;
    }

    /**
     * Data provider for BuildFilter
     *
     * @return array
     */
    public function buildRangeFilterDataProvider()
    {
        return [
            'rangeFilter' => [
                'filter' => $this->createRangeFilter('range1', 0, 10),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_MUST,
                'expectedResult' => '(range1 >= 0 AND range1 < 10)',
            ],
            'rangeFilterNegative' => [
                'filter' => $this->createRangeFilter('range1', 0, 10),
                'conditionType' => RequestBoolQuery::QUERY_CONDITION_NOT,
                'expectedResult' => '(range1 < 0 OR range1 >= 10)',
            ]

        ];
    }

    /**
     * @param $field
     * @param $from
     * @param $to
     * @return \Magento\Framework\Search\Request\Filter\Range|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRangeFilter($field, $from, $to)
    {
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Range::class)
            ->setMethods(['getField', 'getFrom', 'getTo'])
            ->disableOriginalConstructor()
            ->getMock();

        $filter->expects($this->exactly(2))
            ->method('getField')
            ->will($this->returnValue($field));
        $filter->expects($this->atLeastOnce())
            ->method('getFrom')
            ->will($this->returnValue($from));
        $filter->expects($this->atLeastOnce())
            ->method('getTo')
            ->will($this->returnValue($to));
        return $filter;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownFilterType()
    {
        /** @var FilterInterface|\PHPUnit_Framework_MockObject_MockObject $filter */
        $filter = $this->getMockBuilder(\Magento\Framework\Search\Request\FilterInterface::class)
            ->setMethods(['getType'])
            ->getMockForAbstractClass();
        $filter->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('unknownType'));
        $this->builder->build($filter, RequestBoolQuery::QUERY_CONDITION_MUST);
    }
}
