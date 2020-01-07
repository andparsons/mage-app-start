<?php

namespace Magento\RequisitionList\Test\Unit\Model\Action;

/**
 * Unit test for RequestValidator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RequestValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListRepository;

    /**
     * @var \Magento\Framework\App\Console\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator
     */
    private $requestValidator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->moduleConfig = $this->getMockBuilder(\Magento\RequisitionList\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Console\Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['isPost', 'getParam'])
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestValidator = $objectManager->getObject(
            \Magento\RequisitionList\Model\Action\RequestValidator::class,
            [
                '_request' => $this->request,
                'moduleConfig' => $this->moduleConfig,
                'userContext' => $this->userContext,
                'formKeyValidator' => $this->formKeyValidator,
                'resultFactory' => $this->resultFactory,
                'urlBuilder' => $this->urlBuilder,
                'requisitionListRepository' => $this->requisitionListRepository
            ]
        );
    }

    /**
     * Test getResult.
     *
     * @return void
     */
    public function testGetResult()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result->expects($this->once())->method('setPath')
            ->with('customer/account/login')
            ->willReturnSelf();
        $this->userContext->expects($this->atLeastOnce())->method('getUserType')->willReturn(null);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);

        $this->assertEquals($result, $this->requestValidator->getResult($this->request));
    }

    /**
     * Test getResult with NULL result.
     *
     * @return void
     */
    public function testGetResultWithNullResult()
    {
        $userId = 2;
        $this->userContext->expects($this->once())->method('getUserType')->willReturn(
            \Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER
        );
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(1);
        $this->request->expects($this->atLeastOnce())->method('isPost')->willReturn(true);
        $this->formKeyValidator->expects($this->atLeastOnce())->method('validate')->willReturn(true);
        $requisitionList = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListRepository->expects($this->once())->method('get')->willReturn($requisitionList);
        $this->userContext->expects($this->atLeastOnce())->method('getUserId')->willReturn($userId);
        $requisitionList->expects($this->once())->method('getCustomerId')->willReturn($userId);

        $this->assertEquals(null, $this->requestValidator->getResult($this->request));
    }

    /**
     * Test getResult with empty list ID.
     *
     * @return void
     */
    public function testGetResultWithEmptyListId()
    {
        $this->userContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(null);
        $this->request->expects($this->atLeastOnce())->method('isPost')->willReturn(false);
        
        $this->assertEquals(null, $this->requestValidator->getResult($this->request));
    }

    /**
     * Test getResult with referer url.
     *
     * @return void
     */
    public function testGetResultWithRefererUrl()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->userContext->expects($this->once())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(false);
        $result->expects($this->once())->method('setRefererUrl')->willReturnSelf();

        $this->assertEquals($result, $this->requestValidator->getResult($this->request));
    }

    /**
     * Test getResult with exception.
     *
     * @return void
     */
    public function testGetResultWithException()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->userContext->expects($this->atLeastOnce())->method('getUserType')
            ->willReturn(\Magento\Authorization\Model\UserContextInterface::USER_TYPE_CUSTOMER);
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturn(1);
        $this->requisitionListRepository->expects($this->atLeastOnce())->method('get')->willThrowException(
            new \Magento\Framework\Exception\NoSuchEntityException(__('Exception Message'))
        );

        $this->assertEquals($result, $this->requestValidator->getResult($this->request));
    }
}
