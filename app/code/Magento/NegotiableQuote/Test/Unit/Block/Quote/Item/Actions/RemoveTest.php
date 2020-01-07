<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote\Item\Actions;

/**
 * Test for Magento\NegotiableQuote\Block\Quote\Item\Actions\Remove class.
 */
class RemoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postDataHelper;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Item\Actions\Remove
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->postDataHelper = $this->getMockBuilder(\Magento\Framework\Data\Helper\PostHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorization = $this->getMockBuilder(\Magento\Company\Api\AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item\AbstractItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Item\Actions\Remove::class,
            [
                'postDataHelper' => $this->postDataHelper,
                'authorization' => $this->authorization,
                '_urlBuilder' => $this->urlBuilder,
                '_request' => $this->request,
                'item' => $this->item,
            ]
        );
    }

    /**
     * Test getRemoveParams method.
     *
     * @return void
     */
    public function testGetRemoveParams()
    {
        $quoteId = 1;
        $itemId = 3;
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
        $this->item->expects($this->once())->method('getId')->willReturn($itemId);
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with(
                '*/*/itemDelete',
                [
                    'quote_id' => 1,
                    'quote_item_id' => 3
                ]
            )
            ->willReturn('http://test.com/itemDelete/quote_id/1/quote_item_id/3');
        $this->postDataHelper->expects($this->once())
            ->method('getPostData')
            ->with('http://test.com/itemDelete/quote_id/1/quote_item_id/3')
            ->willReturn('post_data');

        $this->assertSame('post_data', $this->block->getRemoveParams());
    }

    /**
     * Test isAllowedManage method.
     *
     * @param bool $isAllowed
     * @param bool $expectedResult
     * @return void
     * @dataProvider isAllowedManageDataProvider
     */
    public function testIsAllowedManage($isAllowed, $expectedResult)
    {
        $this->authorization->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_NegotiableQuote::manage')
            ->willReturn($isAllowed);

        $this->assertEquals($expectedResult, $this->block->isAllowedManage());
    }

    /**
     * Data provider for isAllowedManage method.
     *
     * @return array
     */
    public function isAllowedManageDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
