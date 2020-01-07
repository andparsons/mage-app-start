<?php
namespace Magento\RequisitionList\Test\Unit\Controller\Item;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\RequisitionList\Model\RequisitionListItem\Options\Builder\ConfigurationException;

/**
 * Unit test for Add product item to requisition list.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Magento\RequisitionList\Model\Action\RequestValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestValidator;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\SaveHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemSaveHandler;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListProduct;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Locator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemLocator;

    /**
     * @var \Magento\RequisitionList\Controller\Item\Add
     */
    private $add;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->resultFactory = $this
            ->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->request = $this
            ->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requestValidator = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\Action\RequestValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemSaveHandler = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\SaveHandler::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListProduct = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListProduct::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this
            ->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemLocator = $this->getMockBuilder(
            \Magento\RequisitionList\Model\RequisitionListItem\Locator::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->add = $objectManagerHelper->getObject(
            \Magento\RequisitionList\Controller\Item\Add::class,
            [
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request,
                'messageManager' => $this->messageManager,
                'requestValidator' => $this->requestValidator,
                'requisitionListItemSaveHandler' => $this->requisitionListItemSaveHandler,
                'requisitionListProduct' => $this->requisitionListProduct,
                'logger' => $this->logger,
                'requisitionListItemLocator' => $this->requisitionListItemLocator,
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
        $sku = 'sku';
        $itemId = 1;
        $listId = 2;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku', 'getOptions'])
            ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->willReturnOnConsecutiveCalls($productData, $itemId, $productData, $listId, $listId);
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $productData->expects($this->atLeastOnce())->method('getOptions')->willReturn([]);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('prepareProductData')
            ->willReturn($productData);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('getProduct')->with($sku)
            ->willReturn($product);
        $message = $this->getMockBuilder(\Magento\Framework\Phrase::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requisitionListItemSaveHandler->expects($this->atLeastOnce())->method('saveItem')->willReturn($message);
        $this->messageManager->expects($this->atLeastOnce())->method('addSuccess')->with($message);
        $result->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute() when product with provided sku doesn't exist.
     *
     * @return void
     */
    public function testExecuteWithNotExistingProduct()
    {
        $sku = 'sku';
        $itemId = 1;
        $listId = 2;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku'])
            ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->willReturnOnConsecutiveCalls($productData, $itemId, $productData, $listId, $listId);
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('prepareProductData')
            ->willReturn($productData);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('getProduct')->with($sku)
            ->willReturn(null);
        $this->messageManager->expects($this->atLeastOnce())->method('addError');
        $result->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $this->messageManager->expects($this->never())->method('addSuccess');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute with not configured product.
     *
     * @return void
     */
    public function testExecuteWithNotConfiguredProduct()
    {
        $sku = 'sku';
        $listId = 2;
        $url = 'url';
        $warning = new Phrase('Warning message.');
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setUrl', 'setRefererUrl'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku', 'getOptions'])
            ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getParam')->willReturnOnConsecutiveCalls(
            $productData,
            null,
            $productData,
            $listId,
            $listId,
            $productData,
            $productData
        );
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $productData->expects($this->atLeastOnce())->method('getOptions')->willReturn(null);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('prepareProductData')
            ->willReturn($productData);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance', 'getUrlModel'])
            ->getMockForAbstractClass();
        $this->requisitionListProduct->expects($this->once())->method('getProduct')->with($sku)
            ->willReturn($product);
        $typeInstance = $this->getMockBuilder(AbstractType::class)
            ->disableOriginalConstructor()
            ->setMethods(['processConfiguration'])
            ->getMockForAbstractClass();
        $typeInstance->method('processConfiguration')->willReturn($warning);
        $product->method('getTypeInstance')->willReturn($typeInstance);
        $this->requisitionListItemSaveHandler->expects($this->atLeastOnce())->method('saveItem')
            ->willThrowException(new ConfigurationException($warning));
        $urlModel = $this->getMockBuilder(\Magento\Catalog\Model\Product\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $urlModel->expects($this->atLeastOnce())->method('getUrl')->willReturn($url);
        $product->expects($this->atLeastOnce())->method('getUrlModel')->willReturn($urlModel);
        $this->messageManager->expects($this->atLeastOnce())->method('addWarningMessage')->with($warning);
        $result->expects($this->atLeastOnce())->method('setUrl')->willReturnSelf();
        $this->messageManager->expects($this->never())->method('addSuccess');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute when list with provided ID doesn't exist.
     *
     * @return void
     */
    public function testExecuteWithNotExistingList()
    {
        $sku = 'sku';
        $itemId = 1;
        $listId = 0;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku', 'getOptions'])
            ->getMock();
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(['product_data'], ['item_id'], ['list_id'], ['product_data'], ['list_id'])
            ->willReturnOnConsecutiveCalls($productData, $itemId, $listId, $productData, $listId);
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $productData->expects($this->atLeastOnce())->method('getOptions')->willReturn([]);
        $item = $this->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequisitionListId'])
            ->getMockForAbstractClass();
        $item->expects($this->once())->method('getRequisitionListId')->willReturn($listId);
        $this->requisitionListItemLocator->expects($this->once())
            ->method('getItem')
            ->willReturn($item);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('prepareProductData')
            ->willReturn($productData);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('getProduct')->with($sku)
            ->willReturn($product);
        $message = __('We can\'t specify a requisition list.');
        $this->messageManager->expects($this->atLeastOnce())->method('addError')->with($message);
        $result->expects($this->atLeastOnce())
            ->method('setPath')
            ->with('requisition_list/requisition/index')
            ->willReturnSelf();

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute() with LocalizedException.
     *
     * @return void
     */
    public function testExecuteWithLocalizedException()
    {
        $this->prepareMocksForExecuteWithException();
        $phrase = new \Magento\Framework\Phrase(__('Exception'));
        $exception = new \Magento\Framework\Exception\LocalizedException($phrase);
        $this->requisitionListItemSaveHandler->expects($this->atLeastOnce())->method('saveItem')
            ->willThrowException($exception);
        $this->messageManager->expects($this->atLeastOnce())->method('addError');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute() with Exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $this->prepareMocksForExecuteWithException();
        $exception = new \Exception();
        $this->requisitionListItemSaveHandler->expects($this->atLeastOnce())->method('saveItem')
            ->willThrowException($exception);
        $this->messageManager->expects($this->atLeastOnce())->method('addError');

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute() with Exception and empty item id.
     *
     * @return void
     */
    public function testExecuteWithExceptionAndEmptyItemId()
    {
        $sku = 'SKU1';
        $itemId = null;
        $listId = 2;
        $exceptionMessage = 'Error message';
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setRefererUrl'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku'])
            ->getMock();
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance', 'getUrlModel'])
            ->getMockForAbstractClass();
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('getProduct')->with($sku)
            ->willReturn($product);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->willReturnOnConsecutiveCalls($productData, $itemId, $productData, $listId, $listId);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('prepareProductData')
            ->willReturn($productData);
        $result->expects($this->never())->method('setPath')->willReturnSelf();
        $result->expects($this->atLeastOnce())->method('setRefererUrl')->willReturnSelf();
        $exception = new \Exception($exceptionMessage);
        $this->requisitionListItemSaveHandler->expects($this->atLeastOnce())->method('saveItem')
            ->willThrowException($exception);
        $this->messageManager->expects($this->once())->method('addErrorMessage')
            ->with(__('We can\'t add the item to the Requisition List right now: %1.', $exceptionMessage));

        $this->assertInstanceOf(\Magento\Framework\Controller\ResultInterface::class, $this->add->execute());
    }

    /**
     * Test for execute() with redirect.
     *
     * @return void
     */
    public function testExecuteWithRedirect()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->with('redirect')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn($result);

        $this->assertEquals($result, $this->add->execute());
    }

    /**
     * Prepare mocks for execute with Exception and LocalizedException.
     *
     * @return void
     */
    private function prepareMocksForExecuteWithException()
    {
        $sku = 'sku';
        $itemId = 1;
        $listId = 2;
        $result = $this->getMockBuilder(\Magento\Framework\Controller\ResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPath', 'setRefererUrl'])
            ->getMockForAbstractClass();
        $this->resultFactory->expects($this->atLeastOnce())->method('create')->willReturn($result);
        $this->requestValidator->expects($this->atLeastOnce())->method('getResult')->willReturn(null);
        $productData = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku'])
            ->getMock();
        $productData->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('getProduct')->with($sku)
            ->willReturn($product);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->willReturnOnConsecutiveCalls($productData, $itemId, $productData, $listId, $listId);
        $this->requisitionListProduct->expects($this->atLeastOnce())->method('prepareProductData')
            ->willReturn($productData);
        $result->expects($this->atLeastOnce())->method('setPath')->willReturnSelf();
        $result->expects($this->never())->method('setRefererUrl')->willReturnSelf();
    }
}
