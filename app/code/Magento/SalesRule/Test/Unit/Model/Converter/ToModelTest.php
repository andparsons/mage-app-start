<?php
namespace Magento\SalesRule\Test\Unit\Model\Converter;

class ToModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\SalesRule\Model\Converter\ToModel
     */
    protected $model;

    protected function setUp()
    {
        $this->ruleFactory = $this->getMockBuilder(\Magento\SalesRule\Model\RuleFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->dataObjectProcessor = $this->getMockBuilder(\Magento\Framework\Reflection\DataObjectProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            \Magento\SalesRule\Model\Converter\ToModel::class,
            [
                'ruleFactory' =>  $this->ruleFactory,
                'dataObjectProcessor' => $this->dataObjectProcessor,
            ]
        );
    }

    public function testDataModelToArray()
    {
        $array = [
            'type' => 'conditionType',
            'value' => 'value',
            'attribute' => 'getAttributeName',
            'operator' => 'getOperator',
            'aggregator' => 'getAggregatorType',
            'conditions' => [
                [
                    'type' => null,
                    'value' => null,
                    'attribute' => null,
                    'operator' => null,
                ],
                [
                    'type' => null,
                    'value' => null,
                    'attribute' => null,
                    'operator' => null,
                ],
            ],
        ];

        /**
         * @var \Magento\SalesRule\Model\Data\Condition $dataCondition
         */
        $dataCondition = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Condition::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load', 'getConditionType', 'getValue', 'getAttributeName', 'getOperator',
                'getAggregatorType', 'getConditions'])
            ->getMock();

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getConditionType')
            ->willReturn('conditionType');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn('value');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getAttributeName')
            ->willReturn('getAttributeName');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getOperator')
            ->willReturn('getOperator');

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getAggregatorType')
            ->willReturn('getAggregatorType');

        $dataCondition1 = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Condition::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load', 'getConditionType', 'getValue', 'getAttributeName', 'getOperator',
                'getAggregatorType', 'getConditions'])
            ->getMock();

        $dataCondition2 = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Condition::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load', 'getConditionType', 'getValue', 'getAttributeName', 'getOperator',
                'getAggregatorType', 'getConditions'])
            ->getMock();

        $dataCondition
            ->expects($this->atLeastOnce())
            ->method('getConditions')
            ->willReturn([$dataCondition1, $dataCondition2]);

        $result = $this->model->dataModelToArray($dataCondition);

        $this->assertEquals($array, $result);
    }

    public function testToModel()
    {
        /**
         * @var \Magento\SalesRule\Model\Data\Rule $dataModel
         */
        $dataModel = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load', 'getData', 'getRuleId', 'getCondition', 'getActionCondition',
                'getStoreLabels'])
            ->getMock();
        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getRuleId')
            ->willReturn(1);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getCondition')
            ->willReturn(false);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getActionCondition')
            ->willReturn(false);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([]);

        $ruleModel = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load', 'getId', 'getData'])
            ->getMock();

        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('load')
            ->willReturn($ruleModel);
        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn(['data_1'=>1]);

        $this->dataObjectProcessor
            ->expects($this->any())
            ->method('buildOutputDataArray')
            ->willReturn(['data_2'=>2]);

        $this->ruleFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($ruleModel);

        $result = $this->model->toModel($dataModel);
        $this->assertEquals($ruleModel, $result);
    }

    /**
     * @dataProvider expectedDatesProvider
     */
    public function testFormattingDate($data)
    {
        /**
         * @var \Magento\SalesRule\Model\Data\Rule|\PHPUnit_Framework_MockObject_MockObject $dataModel
         */
        $dataModel = $this->getMockBuilder(\Magento\SalesRule\Model\Data\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'create',
                    'load',
                    'getData',
                    'getRuleId',
                    'getCondition',
                    'getActionCondition',
                    'getStoreLabels',
                    'getFromDate',
                    'setFromDate',
                    'getToDate',
                    'setToDate',
                ]
            )
            ->getMock();
        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getRuleId')
            ->willReturn(null);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getCondition')
            ->willReturn(false);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getActionCondition')
            ->willReturn(false);
        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getStoreLabels')
            ->willReturn([]);
        $ruleModel = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'load', 'getId', 'getData'])
            ->getMock();
        $ruleModel
            ->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn(['data_1'=>1]);

        $this->dataObjectProcessor
            ->expects($this->any())
            ->method('buildOutputDataArray')
            ->willReturn(['data_2'=>2]);

        $this->ruleFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($ruleModel);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getFromDate')
            ->willReturn($data['from_date']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('getToDate')
            ->willReturn($data['to_date']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setFromDate')
            ->with($data['expected_from_date']);

        $dataModel
            ->expects($this->atLeastOnce())
            ->method('setToDate')
            ->with($data['expected_to_date']);

        $this->model->toModel($dataModel);
    }

    /**
     * @return array
     */
    public function expectedDatesProvider()
    {
        return [
            'mm/dd/yyyy to yyyy-mm-dd' => [
                [
                    'from_date' => '03/24/2016',
                    'to_date' => '03/25/2016',
                    'expected_from_date' => '2016-03-24T00:00:00-0700',
                    'expected_to_date' => '2016-03-25T00:00:00-0700',
                ]
            ],
            'yyyy-mm-dd to yyyy-mm-dd' => [
                [
                    'from_date' => '2016-03-24',
                    'to_date' => '2016-03-25',
                    'expected_from_date' => '2016-03-24T00:00:00-0700',
                    'expected_to_date' => '2016-03-25T00:00:00-0700',
                ]
            ],
            'yymmdd to yyyy-mm-dd' => [
                [
                    'from_date' => '20160324',
                    'to_date' => '20160325',
                    'expected_from_date' => '2016-03-24T00:00:00-0700',
                    'expected_to_date' => '2016-03-25T00:00:00-0700',
                ]
            ],
        ];
    }
}
