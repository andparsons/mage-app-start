<?php

namespace Magento\Solr\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Solr\SearchAdapter\FieldMapperInterface;

/**
 * @see \Magento\Solr\SearchAdapter\Filter\Builder\Range
 */
class RangeTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Solr\SearchAdapter\Filter\Builder\Range
     */
    private $target;

    /**
     * @var FieldMapperInterface|MockObject
     */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = $this->getMockBuilder(\Magento\Solr\SearchAdapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            \Magento\Solr\SearchAdapter\Filter\Builder\Range::class,
            [
                'mapper' => $this->mapper
            ]
        );
    }

    /**
     * @param string $field
     * @param string $from
     * @param string $to
     * @param string $expected
     * @dataProvider filterDataProvider
     */
    public function testBuildFilter($field, $from, $to, $expected)
    {
        $this->mapper->expects($this->any())
            ->method('getFieldName')
            ->with($field)->willReturnCallback(function ($field) {
                return 'attr_' . $field;
            });

        $request = $this->createRequestFilter($field, $from, $to);
        $result = $this->target->buildFilter($request);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function filterDataProvider()
    {
        return [
            [
                'field' => 'fieldName1',
                'from' => '10',
                'to' => '25',
                'expected' => 'attr_fieldName1:[10 TO 25]'
            ],
            [
                'field' => 'fieldName2',
                'from' => '',
                'to' => '35',
                'expected' => 'attr_fieldName2:[* TO 35]'
            ],
            [
                'field' => 'fieldName3',
                'from' => '20',
                'to' => '',
                'expected' => 'attr_fieldName3:[20 TO *]'
            ],
            [
                'field' => 'fieldName4',
                'from' => '',
                'to' => '',
                'expected' => 'attr_fieldName4:[* TO *]'
            ]
        ];
    }

    /**
     * @param string $field
     * @param int $from
     * @param int $to
     * @return MockObject|\Magento\Framework\Search\Request\Filter\Range
     */
    private function createRequestFilter($field, $from, $to)
    {
        $requestFilter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Range::class)
            ->disableOriginalConstructor()
            ->setMethods(['getField', 'getFrom', 'getTo'])
            ->getMock();
        $requestFilter->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $requestFilter->expects($this->once())
            ->method('getFrom')
            ->willReturn($from);
        $requestFilter->expects($this->once())
            ->method('getTo')
            ->willReturn($to);
        return $requestFilter;
    }
}
