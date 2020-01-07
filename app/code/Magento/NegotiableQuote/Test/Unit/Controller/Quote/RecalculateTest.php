<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Unit test for Recalculate controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecalculateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Recalculate
     */
    private $recalculate;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRestrictionMock;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagementMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Provider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageProviderMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteAddressMock;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Currency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteCurrencyMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var  \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quote;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuote;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->setMethods(['get', 'save'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->customerRestrictionMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->setMethods(['isOwner', 'isSubUserContent', 'canSubmit'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->negotiableQuoteManagementMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class)
            ->setMethods(['recalculateQuote'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->messageProviderMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Discount\StateChanges\Provider::class)
            ->setMethods(['getChangesMessages'])
            ->disableOriginalConstructor()->getMock();
        $this->negotiableQuoteAddressMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Address::class)
            ->setMethods(['updateQuoteShippingAddressDraft'])
            ->disableOriginalConstructor()->getMock();
        $this->quoteCurrencyMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Quote\Currency::class)
            ->setMethods(['updateQuoteCurrency'])
            ->disableOriginalConstructor()->getMock();

        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->setMethods(['addError'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->resultJson = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->setMethods(['setJsonData'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->negotiableQuote = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class)
            ->setMethods(['getIsRegularQuote', 'getStatus', 'getNegotiatedPriceValue'])
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->recalculate = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Recalculate::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'customerRestriction' => $this->customerRestrictionMock,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagementMock,
                'messageProvider' => $this->messageProviderMock,
                'negotiableQuoteAddress' => $this->negotiableQuoteAddressMock,
                'quoteCurrency' => $this->quoteCurrencyMock,
                '_request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'messageManager' => $this->messageManager
            ]
        );
    }

    /**
     * Prepare Request Mock.
     *
     * @return void
     */
    private function prepareRequestMock()
    {
        $quoteId = 234;
        $this->request->expects($this->once())->method('getParam')->with('quote_id')->willReturn($quoteId);
    }

    /**
     * Prepare Negotiable Quote mock.
     *
     * @return void
     */
    private function prepareNegotiableQuoteMock()
    {
        $isRegularQuote = true;
        $this->negotiableQuote->expects($this->once())->method('getIsRegularQuote')->willReturn($isRegularQuote);
        $negotiatedPrice = 234;
        $this->negotiableQuote->expects($this->once())->method('getNegotiatedPriceValue')
            ->willReturn($negotiatedPrice);
        $negotiableQuoteStatus = NegotiableQuoteInterface::STATUS_EXPIRED;
        $this->negotiableQuote->expects($this->once())->method('getStatus')->willReturn($negotiableQuoteStatus);
    }

    /**
     * Prepare Quote mock.
     *
     * @return void
     */
    private function prepareQuoteMock()
    {
        $extensionAttributes = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->setMethods(['getNegotiableQuote', 'setShippingAssignments'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $extensionAttributes->expects($this->atLeastOnce())->method('getNegotiableQuote')
            ->willReturn($this->negotiableQuote);
        $this->quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->setMethods(['getExtensionAttributes', 'getBaseCurrencyCode', 'getQuoteCurrencyCode'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->quote->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn($extensionAttributes);
    }

    /**
     * Test execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->prepareRequestMock();
        $this->prepareNegotiableQuoteMock();
        $this->prepareQuoteMock();
        $this->quoteRepositoryMock->expects($this->atLeastOnce())->method('get')->willReturn($this->quote);
        $this->customerRestrictionMock->expects($this->exactly(3))->method('isOwner')
            ->willReturnOnConsecutiveCalls(false, true, true);
        $this->customerRestrictionMock->expects($this->once())->method('isSubUserContent')->willReturn(true);
        $this->customerRestrictionMock->expects($this->once())->method('canSubmit')->willReturn(true);
        $this->customerRestrictionMock->expects($this->once())->method('canCurrencyUpdate')->willReturn(true);
        $this->quoteCurrencyMock->expects($this->once())->method('updateQuoteCurrency')->willReturn($this->quote);
        $this->negotiableQuoteAddressMock->expects($this->once())
            ->method('updateQuoteShippingAddressDraft')->willReturn($this->quote);
        $this->negotiableQuoteManagementMock->expects($this->once())->method('recalculateQuote');
        $resultPage = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->setMethods(['addHandle', 'getLayout'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $resultPage->expects($this->exactly(2))
            ->method('addHandle')->with('negotiable_quote_quote_view')->willReturnSelf();
        $layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->setMethods(['getBlock'])
            ->disableOriginalConstructor()->getMock();
        $block = $this->getMockBuilder(\Magento\Framework\View\Element\AbstractBlock::class)
            ->setMethods(['setAdditionalMessage', 'setIsRecalculated', 'toHtml'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $block->expects($this->exactly(2))->method('setAdditionalMessage');
        $block->expects($this->once())->method('setIsRecalculated');
        $blockHtml = '<span>test Message</span>';
        $block->expects($this->exactly(3))->method('toHtml')->willReturn($blockHtml);
        $layout->expects($this->at(0))->method('getBlock')->with('quote.message')->willReturn($block);
        $layout->expects($this->at(1))->method('getBlock')->with('quote_items')->willReturn($block);
        $layout->expects($this->at(2))->method('getBlock')->with('quote.message')->willReturn($block);
        $layout->expects($this->at(3))->method('getBlock')->with('quote.address')->willReturn($block);
        $resultPage->expects($this->exactly(2))->method('getLayout')->willReturn($layout);
        $this->resultJson->expects($this->once())->method('setJsonData')->willReturnSelf();
        $this->resultFactory->expects($this->at(0))->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)->willReturn($resultPage);
        $this->resultFactory->expects($this->at(1))->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);
        $notifications = ['Test notification'];
        $this->messageProviderMock->expects($this->once())->method('getChangesMessages')
            ->willReturn($notifications);
        $this->quoteRepositoryMock->expects($this->once())->method('save');
        $this->quote->expects($this->exactly(2))->method('getBaseCurrencyCode')->willReturn('USD');
        $this->quote->expects($this->exactly(2))->method('getQuoteCurrencyCode')->willReturn('EUR');

        $this->assertEquals($this->resultJson, $this->recalculate->execute());
    }

    /**
     * Test execute() method with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->prepareRequestMock();
        $this->customerRestrictionMock->expects($this->once())->method('isOwner')->willReturn(false);
        $this->customerRestrictionMock->expects($this->once())->method('isSubUserContent')->willReturn(false);
        $this->resultJson->expects($this->once())->method('setJsonData')->willReturnSelf();
        $this->quoteRepositoryMock->expects($this->once())->method('get')->willReturn($this->quote);
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)->willReturn($this->resultJson);
        $this->messageManager->expects($this->once())->method('addError')->willReturnSelf();
        $this->assertEquals($this->resultJson, $this->recalculate->execute());
    }
}
