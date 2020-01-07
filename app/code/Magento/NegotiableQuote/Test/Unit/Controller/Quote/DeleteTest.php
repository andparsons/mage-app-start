<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class DeleteTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Delete
     */
    private $controller;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourse;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resourse = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);

        $redirectFactory =
            $this->createPartialMock(\Magento\Framework\Controller\Result\RedirectFactory::class, ['create']);
        $redirect = $this->createMock(\Magento\Framework\Controller\Result\Redirect::class);
        $redirect->expects($this->any())
            ->method('setPath')->will($this->returnSelf());
        $redirectFactory->expects($this->any())
            ->method('create')->will($this->returnValue($redirect));
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->negotiableQuoteRepository =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface::class);

        $customerRestriction =
            $this->createMock(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class);
        $customerRestriction->expects($this->any())
            ->method('canDelete')->will($this->returnValue(true));
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $this->settingsProvider =
            $this->createPartialMock(\Magento\NegotiableQuote\Model\SettingsProvider::class, ['getCurrentUserId']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->controller = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Delete::class,
            [
                'quoteRepository' => $this->quoteRepository,
                'formKeyValidator' => $this->formKeyValidator,
                'negotiableQuoteRepository' => $this->negotiableQuoteRepository,
                'customerRestriction' => $customerRestriction,
                'messageManager' => $this->messageManager,
                'resultRedirectFactory' => $redirectFactory,
                '_request' => $this->resourse,
                'settingsProvider' => $this->settingsProvider
            ]
        );
    }

    /**
     * @dataProvider getQuoteIds
     *
     * @param int $quoteId
     * @param int $customerId
     * @param int $quoteCustomerId
     * @param \Exception $error
     * @param string $expectedResult
     */
    public function testExecute($quoteId, $customerId, $quoteCustomerId, $error, $expectedResult)
    {
        $this->formKeyValidator->expects($this->any())->method('validate')->willReturn(true);
        $this->resourse->expects($this->any())->method('getParam')->will($this->returnValue($quoteId));
        $quote = $this->createPartialMock(\Magento\Quote\Model\Quote::class, []);
        $quote->setCustomerId($quoteCustomerId);
        $this->quoteRepository->expects($this->any())
            ->method('get')->with($quoteId)->will($this->returnValue($quote));
        $this->settingsProvider->expects($this->any())->method('getCurrentUserId')->willReturn($customerId);
        $negotiableQuote = $this->createMock(\Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface::class);

        if ($error) {
            $this->negotiableQuoteRepository->expects($this->any())
                ->method('getById')->willThrowException($error);
        } else {
            $this->negotiableQuoteRepository->expects($this->any())
                ->method('getById')->will($this->returnValue($negotiableQuote));
        }
        $result = '';
        $messageManager = $this->messageManager;
        $returnCallback = function ($data) use (&$result, $messageManager) {
            $result = $data;
            return $messageManager;
        };
        $returnExceptionCallback = function ($data, $text) use (&$result, $messageManager) {
            $result = $text;
            return $messageManager;
        };
        $this->messageManager->expects($this->any())
            ->method('addErrorMessage')->will($this->returnCallback($returnCallback));
        $this->messageManager->expects($this->any())
            ->method('addSuccessMessage')->will($this->returnCallback($returnCallback));
        $this->messageManager->expects($this->any())
            ->method('addExceptionMessage')->will($this->returnCallback($returnExceptionCallback));

        $this->controller->execute();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function getQuoteIds()
    {
        $ph = new \Magento\Framework\Phrase('test');
        return [
            [1, 2, 2, null, 'You have deleted the quote.'],
            [1, 2, 3, null, ''],
            [1, 2, 3, new \Exception(), 'We can\'t delete the quote right now.'],
            [1, 2, 3, new \Magento\Framework\Exception\LocalizedException($ph), 'test'],
            [null, 2, 2, null, ''],
        ];
    }

    /**
     * Test for method execute without form key
     */
    public function testExecuteWithoutFormkey()
    {
        $result = $this->controller->execute();

        $this->assertInstanceOf(\Magento\Framework\Controller\Result\Redirect::class, $result);
    }
}
