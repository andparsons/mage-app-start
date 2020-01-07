<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column;

/**
 * Abstract class ColumnTest
 */
abstract class ColumnTest extends \PHPUnit\Framework\TestCase
{
    /**#@+*/
    const COLUMN_NAME = 'name';
    /**#@-*/

    /**
     * @var string
     */
    protected $className = '';

    /**
     * @var \Magento\Ui\Component\Listing\Columns\Column
     */
    protected $column;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments($this->className);
        $arguments = $this->setUpPrepareArguments($arguments);
        $this->column = $objectManagerHelper->getObject($this->className, $arguments);
    }

    /**
     * Prepare set up arguments
     *
     * @param array $arguments
     * @return array
     */
    protected function setUpPrepareArguments(array $arguments)
    {
        $context = $arguments['context'];
        $processorMock =
            $this->createMock(\Magento\Framework\View\Element\UiComponent\Processor::class);
        $context->expects($this->never())->method('getProcessor')->will($this->returnValue($processorMock));
        $arguments['data']['name'] = self::COLUMN_NAME;
        return $arguments;
    }

    /**
     * Data provider data source
     * @return array
     */
    public function prepareDataSourceProvider()
    {
        return [
            [
                [
                    'data' => [
                        'items' => [
                            // item 1
                            [
                                'entity_id' => 1,
                                self::COLUMN_NAME => []
                            ],
                            // item 2
                            [
                                'entity_id' => 2,
                                self::COLUMN_NAME => []
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
