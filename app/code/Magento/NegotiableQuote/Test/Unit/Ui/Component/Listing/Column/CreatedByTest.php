<?php

namespace Magento\NegotiableQuote\Test\Unit\Ui\Component\Listing\Column;

/**
 * Unit test for Magento\NegotiableQuote\Ui\Component\Listing\Column\CreatedBy class.
 */
class CreatedByTest extends ColumnTest
{
    /**
     * @var string
     */
    protected $className = \Magento\NegotiableQuote\Ui\Component\Listing\Column\CreatedBy::class;

    /**
     * Prepare arguments for the test.
     *
     * @param array $arguments
     * @return array
     */
    protected function setUpPrepareArguments(array $arguments)
    {
        $arguments = parent::setUpPrepareArguments($arguments);
        $customerMock = $this->getMockBuilder(\Magento\Customer\Model\Data\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock = $arguments['customerRepositoryInterface'];
        $customerNameGenerationMock = $arguments['customerNameGeneration'];
        $creatorMock = $arguments['creator'];
        $customerRepositoryMock->expects($this->atLeastOnce())
            ->method('getById')
            ->willReturn($customerMock);
        $customerNameGenerationMock->expects($this->atLeastOnce())
            ->method('getCustomerName')
            ->with($customerMock)
            ->willReturn('Firstname Lastname');
        $creatorMock->expects($this->atLeastOnce())->method('retrieveCreatorName')->willReturn('');

        $arguments['customerRepositoryInterface'] = $customerRepositoryMock;

        return $arguments;
    }

    /**
     * Test prepareDataSource method.
     *
     * @param array $dataSource
     * @dataProvider prepareDataSourceProvider
     * @return void
     */
    public function testPrepareDataSource(array $dataSource)
    {
        $dataSourceResult = $this->column->prepareDataSource($dataSource);

        foreach ($dataSourceResult['data']['items'] as $item) {
            $this->assertArrayHasKey(self::COLUMN_NAME, $item);
        }
    }

    /**
     * Data provider for prepareDataSource method.
     *
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
                                'customer_id' => 1,
                                'creator_type' => 1,
                                'creator_id' => 1,
                                self::COLUMN_NAME => []
                            ],
                            // item 2
                            [
                                'entity_id' => 2,
                                'customer_id' => 2,
                                'creator_type' => 2,
                                'creator_id' => 1,
                                self::COLUMN_NAME => []
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
