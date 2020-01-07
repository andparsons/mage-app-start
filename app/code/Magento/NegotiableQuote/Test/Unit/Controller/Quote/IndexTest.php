<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller\Quote;

/**
 * Class IndexTest
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteHelper;

    /**
     * @var \Magento\Customer\Model\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRestriction;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $negotiableQuoteManagement;

    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\Index|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->resultPageFactory =
            $this->createPartialMock(\Magento\Framework\View\Result\PageFactory::class, ['create']);
        $resultPage =
            $this->createPartialMock(\Magento\Framework\View\Result\Page::class, ['getConfig', 'getTitle', 'set']);
        $resultPage->expects($this->any())->method('getConfig')->willReturnSelf();
        $resultPage->expects($this->any())->method('getTitle')->willReturnSelf();
        $resultPage->expects($this->any())->method('set')->willReturnSelf();
        $this->resultPageFactory->expects($this->any())->method('create')->willReturn($resultPage);
        $this->resultFactory = $this->createPartialMock(\Magento\Framework\Controller\ResultFactory::class, ['create']);
        $this->resultFactory
            ->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)
            ->willReturn($resultPage);
        $this->quoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->customerUrl = $this->createMock(\Magento\Customer\Model\Url::class);
        $this->resultJsonFactory =
            $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->quoteRepository = $this->createMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->customerRestriction =
            $this->createMock(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class);
        $this->formKeyValidator =
            $this->createMock(\Magento\Framework\Data\Form\FormKey\Validator::class);
        $this->negotiableQuoteManagement =
            $this->createMock(\Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexMock = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\Index::class,
            [
                'resultPageFactory' => $this->resultPageFactory,
                'quoteHelper' => $this->quoteHelper,
                'customerUrl' => $this->customerUrl,
                'resultJsonFactory' => $this->resultJsonFactory,
                'quoteRepository' => $this->quoteRepository,
                'customerRestriction' => $this->customerRestriction,
                'formKeyValidator' => $this->formKeyValidator,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
                'resultFactory' => $this->resultFactory
            ]
        );
    }

    /**
     * Test execute
     */
    public function testExecute()
    {
        $this->assertInstanceOf(\Magento\Framework\View\Result\Page::class, $this->indexMock->execute());
    }
}
