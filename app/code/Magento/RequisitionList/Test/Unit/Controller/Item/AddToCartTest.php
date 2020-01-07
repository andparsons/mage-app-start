<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item;

/**
 * Unit test for AddToCart controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestValidator;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\RequisitionList\Api\RequisitionListManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $listManagement;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\ItemSelector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemSelector;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $response;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \Magento\RequisitionList\Controller\Item\AddToCart
     */
    private $addToCart;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->requestValidator = $this->getMockBuilder(\Magento\RequisitionList\Model\Action\RequestValidator::class)
            ->disableOriginalConstructor()->getMock();
        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->listManagement = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\RequisitionListManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->cartManagement = $this->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this
            ->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsite'])
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->itemSelector = $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList\ItemSelector::class)
            ->disableOriginalConstructor()->getMock();
        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->redirect = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addToCart = $objectManager->getObject(
            \Magento\RequisitionList\Controller\Item\AddToCart::class,
            [
                'requestValidator' => $this->requestValidator,
                'userContext' => $this->userContext,
                'logger' => $this->logger,
                'listManagement' => $this->listManagement,
                'cartManagement' => $this->cartManagement,
                'storeManager' => $this->storeManager,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'itemSelector' => $this->itemSelector,
                '_response' => $this->response,
                '_redirect' => $this->redirect
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $isReplace = true;
        $listId = 1;
        $itemsIds = '2,3';
        $userId = 4;
        $cartId = 5;
        $websiteId = 1;

        $this->requestValidator->expects($this->once())->method('getResult')->with($this->request)->willReturn(null);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->willReturn($result);
        $result->expects($this->once())->method('setRefererUrl')->willReturnSelf();
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(['is_replace', false], ['requisition_id'], ['selected'])
            ->willReturnOnConsecutiveCalls($isReplace, $listId, $itemsIds);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->cartManagement->expects($this->once())
            ->method('createEmptyCartForCustomer')->with($userId)->willReturn($cartId);
        $requisitionListItem = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $itemsIds = explode(',', $itemsIds);
        $this->itemSelector->expects($this->atLeastOnce())->method('selectItemsFromRequisitionList')
            ->with($listId, $itemsIds, $websiteId)->willReturn([$requisitionListItem]);
        $websiteMock = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($websiteMock);
        $websiteMock->expects($this->atLeastOnce())->method('getId')->willReturn($websiteId);
        $this->listManagement->expects($this->once())->method('placeItemsInCart')
            ->with($cartId, [$requisitionListItem], $isReplace)
            ->willReturn([$requisitionListItem]);
        $this->messageManager->expects($this->once())->method('addSuccess')
            ->with(__('You added %1 item(s) to your shopping cart.', 1))->willReturnSelf();
        $this->assertEquals($result, $this->addToCart->execute());
    }

    /**
     * Test for execute method with request validation errors.
     *
     * @return void
     */
    public function testExecuteWithRequestValidationErrors()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->requestValidator->expects($this->once())->method('getResult')->with($this->request)->willReturn($result);
        $this->assertEquals($result, $this->addToCart->execute());
    }

    /**
     * Test for execute method with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $userId = 4;
        $exceptionMesage = 'Cart cannot be created';
        $this->requestValidator->expects($this->once())->method('getResult')->with($this->request)->willReturn(null);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->willReturn($result);
        $result->expects($this->once())->method('setRefererUrl')->willReturnSelf();
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('is_replace', false)->willReturn(false);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->cartManagement->expects($this->once())->method('createEmptyCartForCustomer')->with($userId)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__($exceptionMesage)));
        $this->messageManager->expects($this->once())->method('addError')->with($exceptionMesage)->willReturnSelf();
        $this->assertEquals($result, $this->addToCart->execute());
    }

    /**
     * Test for execute method with InvalidArgumentException.
     *
     * @return void
     */
    public function testExecuteWithInvalidArgumentException()
    {
        $requisitionListId = 1;
        $exceptionMesage = 'Invalid argument';
        $redirectPath = 'requisition_list/requisition/view';

        $this->requestValidator->expects($this->once())->method('getResult')->with($this->request)->willReturn(null);
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willThrowException(new \InvalidArgumentException(__($exceptionMesage)));
        $this->messageManager->expects($this->once())->method('addErrorMessage')->with($exceptionMesage)
            ->willReturnSelf();
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('requisition_id')
            ->willReturn($requisitionListId);
        $this->redirect->expects($this->once())->method('redirect')
            ->with($this->response, $redirectPath, ['requisition_id' => $requisitionListId]);

        $this->assertEquals($this->response, $this->addToCart->execute());
    }

    /**
     * Test for execute method with generic exception.
     *
     * @return void
     */
    public function testExecuteWithGenericException()
    {
        $userId = 4;
        $exception = new \Exception('Cart cannot be created');
        $this->requestValidator->expects($this->once())->method('getResult')->with($this->request)->willReturn(null);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->expects($this->once())->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->willReturn($result);
        $result->expects($this->once())->method('setRefererUrl')->willReturnSelf();
        $this->request->expects($this->atLeastOnce())->method('getParam')->with('is_replace', false)->willReturn(false);
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);
        $this->cartManagement->expects($this->once())
            ->method('createEmptyCartForCustomer')->with($userId)->willThrowException($exception);
        $this->messageManager->expects($this->once())
            ->method('addError')->with('Something went wrong.')->willReturnSelf();
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->assertEquals($result, $this->addToCart->execute());
    }
}
