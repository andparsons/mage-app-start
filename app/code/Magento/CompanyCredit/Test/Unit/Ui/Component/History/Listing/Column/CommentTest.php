<?php

namespace Magento\CompanyCredit\Test\Unit\Ui\Component\History\Listing\Column;

/**
 * Test for \Magento\CompanyCredit\Ui\Component\History\Listing\Column\Comment class.
 */
class CommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CompanyCredit\Ui\Component\History\Listing\Column\Comment
     */
    private $commentColumn;

    /**
     * @var \Magento\CompanyCredit\Model\Sales\OrderLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderLocator;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->orderLocator = $this->getMockBuilder(\Magento\CompanyCredit\Model\Sales\OrderLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();
        $escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper->expects($this->any())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        $context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $serializer = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->commentColumn = $objectManager->getObject(
            \Magento\CompanyCredit\Ui\Component\History\Listing\Column\Comment::class,
            [
                'context' => $context,
                'orderLocator' => $this->orderLocator,
                'urlBuilder' => $this->urlBuilder,
                'escaper' => $escaper,
                'serializer' => $serializer
            ]
        );
        $this->commentColumn->setData('name', 'comment');
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSourceWithCustomComment()
    {
        $dataSource = ['data' => ['items' => [['type' => 1, 'comment' => json_encode(['custom' => 'test'])]]]];
        $expected = ['data' => ['items' => [['type' => 1, 'comment' => 'test']]]];

        $this->assertEquals($expected, $this->commentColumn->prepareDataSource($dataSource));
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSourceWithOrder()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['type' => 1, 'comment' => json_encode(['system' => ['order' => 1111]])]
                ]
            ]
        ];
        $expected = [
            'data' => [
                'items' => [
                    ['type' => 1, 'comment' => 'Order # <a href="sales/order/view/order_id/1"">1111</a>']
                ]
            ]
        ];
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderLocator->expects($this->once())
            ->method('getOrderByIncrementId')
            ->willReturn($order);
        $order->expects($this->once())->method('getIncrementId')->willReturn('1111');
        $order->expects($this->once())->method('getEntityId')->willReturn(1);
        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->with('sales/order/view', ['order_id' => 1])->willReturn('sales/order/view/order_id/1');

        $this->assertEquals($expected, $this->commentColumn->prepareDataSource($dataSource));
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSourceWithOrderWithException()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    ['type' => 1, 'comment' => json_encode(['system' => ['order' => '1']])]
                ]
            ]
        ];
        $expected = [
            'data' => ['items' => [['type' => 1, 'comment' => '']]]
        ];

        $this->orderLocator->expects($this->once())
            ->method('getOrderByIncrementId')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());

        $this->assertEquals($expected, $this->commentColumn->prepareDataSource($dataSource));
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSourceWithExceedLimitNo()
    {
        $comment = json_encode(
            [
                'system' => [
                    'exceed_limit' => [
                        'value' => false,
                        'company_name' => 'test',
                        'user_name' => 'user'
                    ]
                ]
            ]
        );

        $dataSource = ['data' => ['items' => [['type' => 1, 'comment' => $comment]]]];
        $expected = [
            'data' => [
                'items' => [
                    ['type' => 1, 'comment' => 'user made an update. test cannot exceed the Credit Limit.']
                ]
            ]
        ];

        $this->assertEquals($expected, $this->commentColumn->prepareDataSource($dataSource));
    }

    /**
     * Test method for prepareDataSource.
     *
     * @return void
     */
    public function testPrepareDataSourceWithExceedLimitYes()
    {
        $comment = json_encode(
            [
                'system' => [
                    'exceed_limit' => [
                        'value' => true,
                        'company_name' => 'test',
                        'user_name' => 'user'
                    ]
                ]
            ]
        );

        $dataSource = ['data' => ['items' => [['type' => 1, 'comment' => $comment]]]];
        $expected = [
            'data' => [
                'items' => [
                    ['type' => 1, 'comment' => 'user made an update. test can exceed the Credit Limit.']
                ]
            ]
        ];

        $this->assertEquals($expected, $this->commentColumn->prepareDataSource($dataSource));
    }
}
