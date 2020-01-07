<?php

namespace Magento\Solr\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Solr\SearchAdapter\FieldMapperInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @see \Magento\Solr\SearchAdapter\Filter\Builder\Wildcard
 */
class WildcardTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Solr\SearchAdapter\Filter\Builder\Wildcard
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
            \Magento\Solr\SearchAdapter\Filter\Builder\Wildcard::class,
            [
                'mapper' => $this->mapper
            ]
        );
    }

    /**
     * @param $field
     * @param $value
     * @param $expected
     * @dataProvider filterDataProvider
     */
    public function testBuildFilter($field, $value, $expected)
    {
        $this->mapper->expects($this->any())
            ->method('getFieldName')
            ->with($field)->willReturnCallback(function ($field) {
                return 'attr_' . $field;
            });

        $request = $this->createRequestFilter($field, $value);
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
                'value' => 'someValue',
                'expected' => 'attr_fieldName1:(*someValue*)'
            ]
        ];
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return \Magento\Framework\Search\Request\Filter\Range|MockObject
     */
    private function createRequestFilter($field, $value)
    {
        $requestFilter = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Range::class)
            ->disableOriginalConstructor()
            ->setMethods(['getField', 'getValue'])
            ->getMock();
        $requestFilter->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $requestFilter->expects($this->once())
            ->method('getValue')
            ->willReturn($value);
        return $requestFilter;
    }
}
