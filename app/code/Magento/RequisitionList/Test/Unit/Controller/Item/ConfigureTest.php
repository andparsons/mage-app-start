<?php

namespace Magento\RequisitionList\Test\Unit\Controller\Item;

/**
 * Unit test for configure.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestValidator;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionList\Items|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactory;

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsManagement;

    /**
     * @var \Magento\Catalog\Helper\Product\View|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productViewHelper;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\RequisitionList\Controller\Item\Configure
     */
    private $configure;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->requestValidator = $this->getMockBuilder(\Magento\RequisitionList\Model\Action\RequestValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemRepository = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionList\Items::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageFactory = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->dataObjectFactory = $this->getMockBuilder(\Magento\Framework\DataObjectFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->optionsManagement = $this->getMockBuilder(\Magento\RequisitionList\Model\OptionsManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productViewHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Product\View::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepareAndRender'])
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addError'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->configure = $objectManager->getObject(
            \Magento\RequisitionList\Controller\Item\Configure::class,
            [
                '_request' => $this->request,
                'resultFactory' => $this->resultFactory,
                'requestValidator' => $this->requestValidator,
                'requisitionListItemRepository' => $this->requisitionListItemRepository,
                'resultPageFactory' => $this->resultPageFactory,
                'dataObjectFactory' => $this->dataObjectFactory,
                'optionsManagement' => $this->optionsManagement,
                'productViewHelper' => $this->productViewHelper,
                'logger' => $this->logger,
                'messageManager' => $this->messageManager,
            ]
        );
    }

    /**
     * Test for execute().
     *
     * @return void
     */
    public function testExecute()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $resultPage = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemRepository->expects($this->any())->method('get')->willReturn($item);
        $params = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $params->expects($this->atLeastOnce())->method('setData')->willReturnSelf();
        $this->dataObjectFactory->expects($this->any())->method('create')->willReturn($params);
        $this->productViewHelper->expects($this->atLeastOnce())->method('prepareAndRender')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->configure->execute());
    }

    /**
     * Test for execute() with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $resultPage = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);
        $phrase = new \Magento\Framework\Phrase('Exception');
        $exception = new \Magento\Framework\Exception\LocalizedException(__($phrase));
        $this->requisitionListItemRepository->expects($this->any())->method('get')->willThrowException($exception);
        $this->messageManager->expects($this->atLeastOnce())->method('addError')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->configure->execute());
    }

    /**
     * Test for execute() with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->request->expects($this->any())->method('getParam')->willReturn(1);
        $resultPage = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMock();
        $this->resultFactory->expects($this->any())->method('create')->willReturn($resultPage);
        $exception = new \Exception();
        $this->requisitionListItemRepository->expects($this->any())->method('get')->willThrowException($exception);

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->configure->execute());
    }
}
