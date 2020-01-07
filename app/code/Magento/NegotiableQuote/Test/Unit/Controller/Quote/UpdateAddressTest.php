<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

use Magento\Framework\Phrase;

/**
 * Class UpdateAddressTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRestriction;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\UpdateAddress
     */
    private $updateAddress;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Magento\Framework\Message\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJson;

    /**
     * @var \Magento\NegotiableQuote\Model\Quote\Address|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteAddress;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPost'])
            ->getMockForAbstractClass();
        $this->resultJson = $this->createPartialMock(
            \Magento\Framework\Controller\Result\Json::class,
            ['setData']
        );
        $this->settingsProvider = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\SettingsProvider::class,
            ['retrieveJsonError', 'retrieveJsonSuccess']
        );
        $this->messageManager = $this->createMock(\Magento\Framework\Message\Manager::class);
        $this->quoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->customerRestriction = $this->createMock(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        );
        $this->negotiableQuoteManagement = $this->createMock(
            \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class
        );
        $this->negotiableQuoteAddress = $this->createMock(
            \Magento\NegotiableQuote\Model\Quote\Address::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->updateAddress = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\UpdateAddress::class,
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager,
                'quoteHelper' => $this->quoteHelper,
                'quoteRepository' => $this->quoteRepository,
                'customerRestriction' => $this->customerRestriction,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'settingsProvider' => $this->settingsProvider,
                'negotiableQuoteAddress' => $this->negotiableQuoteAddress
            ]
        );
    }

    /**
     * Test for method execute.
     *
     * @return void
     */
    public function testExecute()
    {
        $this->request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $this->request->expects($this->at(1))->method('getParam')->with('quote_id')->willReturn(1);
        $this->request->expects($this->at(2))->method('getParam')->with('address_id')->willReturn(1);
        $quote = $this->createPartialMock(
            \Magento\Quote\Model\Quote::class,
            ['getExtensionAttributes', 'getShippingAddress']
        );
        $this->quoteRepository->expects($this->once())->method('get')->with(1)->willReturn($quote);
        $this->customerRestriction->expects($this->once())->method('canSubmit')->willReturn(true);
        $this->negotiableQuoteAddress->expects($this->once())->method('updateAddress')->with(1, 1);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonSuccess')->willReturn($this->resultJson);
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->updateAddress->execute());
    }

    /**
     * Test for method execute not POST.
     *
     * @return void
     */
    public function testExecuteNotIsPost()
    {
        $this->request->expects($this->atLeastOnce())->method('isPost')->willReturn(false);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->settingsProvider->expects($this->once())->method('retrieveJsonError')->willReturn($this->resultJson);
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->updateAddress->execute());
    }

    /**
     * Test execute with no such entity exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function testExecuteWithNoSuchEntityException()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $phrase = new Phrase('Requested quote was not found');
        $this->request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $this->request->expects($this->at(1))->method('getParam')->with('quote_id')->willReturn(1);
        $this->request->expects($this->at(2))->method('getParam')->with('address_id')->willReturn(1);
        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class
        );
        $this->quoteRepository->expects($this->once())->method('get')->with(1)->willReturn($quote);
        $this->customerRestriction->expects($this->once())->method('canSubmit')->willReturn(false);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonSuccess')->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with($phrase);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonError')->willThrowException($exception);
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->updateAddress->execute());
    }

    /**
     * Test execute with exception.
     *
     * @expectedException \Exception
     * @return void
     */
    public function testExecuteWithException()
    {
        $exception = new \Exception();
        $phrase = new Phrase('Something went wrong. Please try again later.');
        $this->request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $this->request->expects($this->at(1))->method('getParam')->with('quote_id')->willReturn(1);
        $this->request->expects($this->at(2))->method('getParam')->with('address_id')->willReturn(1);
        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class
        );
        $this->quoteRepository->expects($this->once())->method('get')->with(1)->willReturn($quote);
        $this->customerRestriction->expects($this->once())->method('canSubmit')->willReturn(false);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonSuccess')->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with($phrase);
        $this->settingsProvider->expects($this->once())->method('retrieveJsonError')->willThrowException($exception);
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Json::class, $this->updateAddress->execute());
    }
}
