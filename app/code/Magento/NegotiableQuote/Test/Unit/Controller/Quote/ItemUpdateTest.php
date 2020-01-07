<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class ItemUpdateTest.
 */
class ItemUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRestriction;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\ItemUpdate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemUpdateMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->request->expects($this->at(0))->method('getParam')->with('quote_id')->willReturn(1);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $resultRedirectFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RedirectFactory::class, ['create']);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $resultRedirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $resultRedirectFactory->expects($this->atLeastOnce())->method('create')->willReturn($resultRedirect);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->customerRestriction =
            $this->createMock(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class);
        $this->negotiableQuoteManagement =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class);
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $this->settingsProvider =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\SettingsProvider::class, ['getCurrentUserId']);
        $this->itemUpdateMock = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\ItemUpdate::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'customerRestriction' => $this->customerRestriction,
                'formKeyValidator' => $this->formKeyValidator,
                'resultRedirectFactory' => $resultRedirectFactory,
                '_request' => $this->request,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'settingsProvider' => $this->settingsProvider,
                'messageManager' => $this->messageManager,
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
        $this->messageManager->expects($this->never())->method('addSuccessMessage');
        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $this->itemUpdateMock->execute());
    }

    /**
     * Test for method execute with right validation.
     *
     * @return void
     */
    public function testExecuteWithRightValidation()
    {
        $cartData = [
            10 => [
                'qty' => 5,
                'before_suggest_qty' => 5,
            ]
        ];
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, ['getCustomerId'], []);
        $quote->expects($this->any())->method('getCustomerId')->willReturn(1);
        $this->formKeyValidator->expects($this->once())->method('validate')->with($this->request)->willReturn('true');
        $this->quoteRepository->expects($this->once())->method('get')->willReturn($quote);
        $this->customerRestriction->expects($this->atLeastOnce())->method('canSubmit')->willReturn(true);
        $this->settingsProvider->expects($this->once())->method('getCurrentUserId')->willReturn(1);
        $this->request->expects($this->at(1))->method('getParam')->with('cart')->willReturn($cartData);
        $this->negotiableQuoteManagement->expects($this->once())->method('updateQuoteItems')->with(1, $cartData);
        $this->negotiableQuoteManagement->expects($this->once())
            ->method('updateProcessingByCustomerQuoteStatus')
            ->with(1);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')->willReturn(__('You have updated the items in the quote.'));
        $this->itemUpdateMock->execute();
    }
}
