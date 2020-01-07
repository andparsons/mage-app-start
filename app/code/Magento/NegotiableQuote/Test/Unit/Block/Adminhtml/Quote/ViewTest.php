<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;

/**
 * Unit test for View.
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View
     */
    private $view;

    /**
     * Test construct.
     *
     * @param string $status
     * @param bool $canSubmit
     * @param array $buttonsExpect
     * @return void
     * @dataProvider constructDataProvider
     */
    public function testConstruct($status, $canSubmit, $buttonsExpect)
    {
        $request = $this->getMockForAbstractClass(\Magento\Framework\App\RequestInterface::class, [], '', false);
        $request->expects($this->atLeastOnce())->method('getParam')->willReturn(1);
        $buttonList = $this->getMockBuilder(\Magento\Backend\Block\Widget\Button\ButtonList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buttons = [];
        $addButtonCallback = function ($buttonId, $data) use (&$buttons) {
            if (!isset($data['disabled']) || (isset($data['disabled']) && ($data['disabled'] === false))) {
                $buttons[] = $buttonId;
            }
        };
        $buttonList->expects($this->any())->method('add')->will($this->returnCallback($addButtonCallback));
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteNegotiation = $this->getMockBuilder(\Magento\NegotiableQuote\Model\NegotiableQuote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteNegotiation->expects($this->any())->method('getStatus')->willReturn($status);
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $extensionAttributes->expects($this->any())->method('getNegotiableQuote')
            ->will($this->returnValue($quoteNegotiation));
        $quote->expects($this->any())->method('getExtensionAttributes')
            ->will($this->returnValue($extensionAttributes));
        $this->restriction = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\Admin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->restriction->expects($this->atLeastOnce())->method('canSubmit')->willReturn($canSubmit);
        $this->restriction->setQuote($quote);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->view = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View::class,
            [
                'restriction' => $this->restriction,
                'data' => [],
                'buttonList' => $buttonList,
                '_request' => $request
            ]
        );

        $this->assertEquals($buttonsExpect, $buttons);
    }

    /**
     * DataProvider for testConstruct().
     *
     * @return array
     */
    public function constructDataProvider()
    {
        return [
            [
                NegotiableQuote::STATUS_CREATED,
                true,
                ['back', 'quote_print', 'quote_save', 'quote_decline', 'quote_send']
            ],
            [
                NegotiableQuote::STATUS_ORDERED,
                false,
                ['back', 'quote_print']
            ]
        ];
    }
}
