<?php

namespace Magento\Solr\Test\Unit\SearchAdapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConditionManagerTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Solr\SearchAdapter\ConditionManager */
    private $conditionManager;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['quote', 'quoteIdentifier'])
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())
            ->method('quote')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return sprintf('\'%s\'', $value);
                    }
                )
            );
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return sprintf('`%s`', $value);
                    }
                )
            );

        $this->conditionManager = $objectManager->getObject(
            \Magento\Solr\SearchAdapter\ConditionManager::class,
            []
        );
    }

    /**
     * @dataProvider wrapBracketsDataProvider
     * @param $query
     * @param $expectedResult
     */
    public function testWrapBrackets($query, $expectedResult)
    {
        $actualResult = $this->conditionManager->wrapBrackets($query);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for wrapBrackets test
     *
     * @return array
     */
    public function wrapBracketsDataProvider()
    {
        return [
            'validQuery' => [
                'query' => 'a = b',
                'expectedResult' => '(a = b)',
            ],
            'emptyQuery' => [
                'query' => '',
                'expectedResult' => '',
            ],
            'invalidQuery' => [
                'query' => '1',
                'expectedResult' => '(1)',
            ]
        ];
    }

    public function testCombineQueries()
    {
        $queries = [
            'a = b',
            false,
            true,
            '',
            0,
            'test',
        ];
        $unionOperator = 'AND';
        $expectedResult = 'a = b AND 1 AND 0 AND test';
        $actualResult = $this->conditionManager->combineQueries($queries, $unionOperator);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider generateConditionDataProvider
     * @param $field
     * @param $value
     * @param $expectedResult
     */
    public function testGenerateCondition($field, $value, $expectedResult)
    {
        $actualResult = $this->conditionManager->generateCondition($field, $value);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function generateConditionDataProvider()
    {
        return [
            [
                'field' => 'a',
                'value' => 1,
                'expectedResult' => 'a:"1"'
            ],
            [
                'field' => 'a',
                'value' => '123',
                'expectedResult' => 'a:"123"'
            ],
        ];
    }
}
