<?php
namespace Magento\NegotiableQuote\Test\Unit\Plugin\Checkout\Controller\Index;

/**
 * Unit test for IndexPlugin.
 */
class IndexPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restriction;

    /**
     * @var \Magento\Company\Model\CompanyUserPermission|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyUserPermission;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerContext;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Checkout\Controller\Index\IndexPlugin
     */
    private $indexPlugin;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resultRedirectFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->restriction = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class)
            ->setMethods(['canProceedToCheckout'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyUserPermission = $this->getMockBuilder(\Magento\Company\Model\CompanyUserPermission::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->indexPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Checkout\Controller\Index\IndexPlugin::class,
            [
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'quoteRepository' => $this->quoteRepository,
                'restriction' => $this->restriction,
                'companyUserPermission' => $this->companyUserPermission,
                'customerContext' => $this->customerContext,
                'request' => $this->request
            ]
        );
    }

    /**
     * Test aroundExecute.
     *
     * @param bool $isCurrentUserCompanyUser
     * @param int $quoteId
     * @param string $path
     * @return void
     * @dataProvider testAroundExecuteDataProvider
     */
    public function testAroundExecute($quoteId, $isCurrentUserCompanyUser, $path)
    {
        $this->request->expects($this->once())->method('getParam')->with('negotiableQuoteId')->willReturn($quoteId);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteRepository->expects($this->once())->method('get')->with($quoteId)->willReturn($quote);
        $this->restriction->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->restriction->expects($this->once())->method('canProceedToCheckout')->willReturn(false);
        $this->companyUserPermission->expects($this->once())->method('isCurrentUserCompanyUser')
            ->willReturn($isCurrentUserCompanyUser);
        $resultRedirect = $this->getMockForAbstractClass(
            \Magento\Framework\Controller\ResultInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setPath']
        );
        $resultRedirect->expects($this->once())->method('setPath')->with($path)->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $subject = $this->getMockBuilder(\Magento\Checkout\Controller\Index\Index::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function ($object) {
            return $object;
        };
        $this->customerContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->customerContext->expects($this->once())->method('getUserId')->willReturn(1);

        $this->assertInstanceOf(
            \Magento\Framework\Controller\ResultInterface::class,
            $this->indexPlugin->aroundExecute($subject, $proceed)
        );
    }

    /**
     * Test aroundExecute with guest user.
     */
    public function testAroundExecuteForGuest()
    {
        $this->request->expects($this->once())->method('getParam')->with('negotiableQuoteId')->willReturn(1);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteRepository->expects($this->once())->method('get')->with(1)->willReturn($quote);
        $this->restriction->expects($this->once())->method('setQuote')->with($quote)->willReturnSelf();
        $this->restriction->expects($this->once())->method('canProceedToCheckout')->willReturn(false);
        $this->companyUserPermission->expects($this->never())->method('isCurrentUserCompanyUser');
        $resultRedirect = $this->getMockForAbstractClass(
            \Magento\Framework\Controller\ResultInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['setPath']
        );
        $resultRedirect->expects($this->once())->method('setPath')->with('customer/account/login')->willReturnSelf();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($resultRedirect);
        $subject = $this->getMockBuilder(\Magento\Checkout\Controller\Index\Index::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function ($object) {
            return $object;
        };
        $this->customerContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_GUEST);
        $this->customerContext->expects($this->once())->method('getUserId')->willReturn(1);

        $this->assertInstanceOf(
            \Magento\Framework\Controller\ResultInterface::class,
            $this->indexPlugin->aroundExecute($subject, $proceed)
        );
    }

    /**
     * Test aroundExecute with NoSuchEntityException.
     *
     * @return void
     */
    public function testAroundExecuteWithNoSuchEntityException()
    {
        $this->request->expects($this->once())->method('getParam')->with('negotiableQuoteId')->willReturn(1);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->quoteRepository->expects($this->once())->method('get')->willThrowException($exception);
        $this->restriction->expects($this->once())->method('canProceedToCheckout')->willReturn(false);
        $subject = $this->getMockBuilder(\Magento\Checkout\Controller\Index\Index::class)
            ->disableOriginalConstructor()
            ->getMock();
        $proceed = function () {
            return null;
        };

        $this->indexPlugin->aroundExecute($subject, $proceed);
    }

    /**
     * DataProvider testAroundExecute.
     *
     * @return array
     */
    public function testAroundExecuteDataProvider()
    {
        return [
            [1, true, 'company/accessdenied'],
            [1, false, 'noroute'],
        ];
    }
}
