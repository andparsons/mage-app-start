<?php

namespace Magento\NegotiableQuote\Test\Unit\Controller;

use Magento\NegotiableQuote\Model\Restriction\RestrictionInterfaceFactory;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;

/**
 * Test Magento\NegotiableQuote\Controller\Quote abstract class using Magento\NegotiableQuote\Controller\Quote\View.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Controller\Quote\View
     */
    private $quoteController;

    /**
     * @var \Magento\NegotiableQuote\Model\SettingsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingsProvider;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelper;

    /**
     * @var \Magento\Framework\App\ActionFlag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $actionFlag;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRestriction;

    /**
     * @var RestrictionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $restrictionFactory;

    /**
     * @var NegotiableQuoteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteManagement;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['isAjax', 'getFullActionName', 'getRouteName', 'isDispatched', 'initForward', 'setDispatched']
            )
            ->getMockForAbstractClass();
        $this->settingsProvider = $this->getMockBuilder(\Magento\NegotiableQuote\Model\SettingsProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->actionFlag = $this->getMockBuilder(\Magento\Framework\App\ActionFlag::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerRestriction = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->restrictionFactory = $this->getMockBuilder(RestrictionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->negotiableQuoteManagement = $this->getMockBuilder(NegotiableQuoteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->quoteController = $objectManager->getObject(
            \Magento\NegotiableQuote\Controller\Quote\View::class,
            [
                'settingsProvider' => $this->settingsProvider,
                'quoteHelper' => $this->quoteHelper,
                'customerRestriction' => $this->customerRestriction,
                '_request' => $this->request,
                '_actionFlag' => $this->actionFlag,
                '_redirect' => $this->redirect,
                '_response' => $this->response,
                'restrictionFactory' => $this->restrictionFactory,
                'negotiableQuoteManagement' => $this->negotiableQuoteManagement,
            ]
        );
    }

    /**
     * Test dispatch method.
     *
     * @return void
     */
    public function testDispatch(): void
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingsProvider->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->settingsProvider->expects($this->once())
            ->method('getCurrentUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN);
        $this->request->expects($this->once())->method('isAjax')->willReturn(true);
        $this->settingsProvider->expects($this->once())
            ->method('getCustomerLoginUrl')
            ->willReturn('customer/account/login');
        $this->settingsProvider->expects($this->once())
            ->method('retrieveJsonError')
            ->with('', 'customer/account/login')
            ->willReturn($result);

        $this->assertEquals($result, $this->quoteController->dispatch($this->request));
    }

    /**
     * Test dispatch with FLAG_NO_DISPATCH.
     *
     * @return void
     */
    public function testDispatchWithFlagNoDispatch(): void
    {
        $this->settingsProvider->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->settingsProvider->expects($this->once())
            ->method('getCurrentUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->quoteHelper->expects($this->once())->method('getCurrentUserId')->willReturn(null);
        $this->actionFlag->expects($this->once())->method('set')->with()->willReturnSelf('', 'no-dispatch', true);

        $this->assertInstanceOf(
            \Magento\Framework\App\ResponseInterface::class,
            $this->quoteController->dispatch($this->request)
        );
    }

    /**
     * Test dispatch with enabled quote but with disabled resource.
     *
     * @param bool $isCurrentUserCompanyUser
     * @param string $redirectPath
     * @dataProvider dispatchWithDisabledQuoteDataProvider
     * @return void
     */
    public function testDispatchWithDisabledQuote(bool $isCurrentUserCompanyUser, string $redirectPath): void
    {
        $this->settingsProvider->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->settingsProvider->expects($this->once())
            ->method('getCurrentUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->quoteHelper->expects($this->once())->method('getCurrentUserId')->willReturn(1);
        $this->settingsProvider->expects($this->once())
            ->method('isCurrentUserCompanyUser')
            ->willReturn($isCurrentUserCompanyUser);
        $this->redirect->expects($this->atLeastOnce())->method('redirect')->with($this->response, $redirectPath, []);
        $this->quoteHelper->expects($this->atLeastOnce())->method('isEnabled')->willReturn(false);

        $this->assertInstanceOf(
            \Magento\Framework\App\ResponseInterface::class,
            $this->quoteController->dispatch($this->request)
        );
    }

    /**
     * Data provider for dispatch() with disabled quote.
     *
     * @return array
     */
    public function dispatchWithDisabledQuoteDataProvider(): array
    {
        return [
            [true, 'company/accessdenied'],
            [false, 'noroute']
        ];
    }

    /**
     * Test dispatch with Exception.
     *
     * @expectedException \Magento\Framework\Exception\NotFoundException
     * @return void
     */
    public function testDispatchWithException(): void
    {
        $this->settingsProvider->expects($this->once())->method('isModuleEnabled')->willReturn(false);
        $this->quoteController->dispatch($this->request);
    }
}
