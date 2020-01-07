<?php

namespace Magento\SharedCatalog\Test\Unit\Controller\Adminhtml\SharedCatalog\Configure;

use Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk;

/**
 * Unit test for save configuration controller.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Configure\Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configureCategory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\WizardFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorageFactory;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $wizardStorage;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Backend\Model\View\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var ScheduleBulk|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleBulk;

    /**
     * @var \Magento\SharedCatalog\Api\PriceManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceSharedCatalogManagement;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManager;

    /**
     * @var \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Save
     */
    private $save;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\Wizard|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\SharedCatalog\Model\Form\Storage\DiffProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $diffProcessor;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->setMethods(['get', 'save'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Backend\Model\View\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->wizardStorage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scheduleBulk = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->configureCategory = $this->getMockBuilder(\Magento\SharedCatalog\Model\Configure\Category::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\Form\Storage\WizardFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scheduleBulk = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\ScheduleBulk::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceSharedCatalogManagement = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\PriceManagementInterface::class
        )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->getMockBuilder(
            \Magento\Backend\Model\View\Result\RedirectFactory::class
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->userContext = $this->getMockBuilder(\Magento\Authorization\Model\UserContextInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->diffProcessor = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\DiffProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDiff'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->save = $objectManager->getObject(
            \Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog\Configure\Save::class,
            [
                'configureCategory' => $this->configureCategory,
                'wizardStorageFactory' => $this->wizardStorageFactory,
                'logger' => $this->logger,
                'scheduleBulk' => $this->scheduleBulk,
                'priceSharedCatalogManagement' => $this->priceSharedCatalogManagement,
                'userContextInterface' => $this->userContext,
                'diffProcessor' => $this->diffProcessor,
                '_request' => $this->request,
                'resultRedirectFactory' => $this->resultRedirectFactory,
                'messageManager' => $this->messageManager
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
        $changes = [
            'pricesChanged' => false,
            'categoriesChanged' => false,
            'productsChanged' => true,
        ];

        $this->prepareExecuteBody();
        $this->diffProcessor->expects($this->once())
            ->method('getDiff')
            ->willReturn($changes);
        $message = __(
            'The selected changes have been applied to the shared catalog.'
        );
        $this->messageManager->expects($this->once())->method('addSuccessMessage')
            ->with($message)->willReturnSelf();
        $result = $this->prepareExecuteResultMock();

        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Test for method execute with success message about changed categories.
     *
     * @return void
     */
    public function testExecuteWithMessageAboutChangedCategories()
    {
        $changes = [
            'pricesChanged' => false,
            'categoriesChanged' => true
        ];

        $this->prepareExecuteBody();
        $this->diffProcessor->expects($this->once())
            ->method('getDiff')
            ->willReturn($changes);
        $message = __(
            'The selected items are being processed. You can continue to work in the meantime.'
        );

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with($message)
            ->willReturnSelf();
        $result = $this->prepareExecuteResultMock();

        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Prepare Result mock for execute() method test.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareExecuteResultMock()
    {
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())
            ->method('setPath')->with('shared_catalog/sharedCatalog/index')->willReturnSelf();

        return $result;
    }

    /**
     * Prepare body for execute() method test.
     *
     * @return void
     */
    private function prepareExecuteBody()
    {
        $configurationKey = 'configuration_key';
        $sharedCatalogId = 1;
        $storeId = 2;
        $productSkus = ['sku1', 'sku2'];
        $tierPrices = [3 => 10, 4 => 15, 5 => 20];

        $this->request->expects($this->at(0))->method('getParam')->with('catalog_id')->willReturn($sharedCatalogId);
        $this->request->expects($this->at(1))
            ->method('getParam')->with('configure_key')->willReturn($configurationKey);
        $this->request->expects($this->at(2))->method('getParam')->with('store_id')->willReturn($storeId);
        $this->storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory
            ->expects($this->once())->method('create')->with(['key' => $configurationKey])->willReturn($this->storage);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configureCategory->expects($this->once())->method('saveConfiguredCategories')
            ->with($this->storage, $sharedCatalogId, $storeId)->willReturn($sharedCatalog);
        $this->storage->expects($this->once())->method('getUnassignedProductSkus')->willReturn($productSkus);
        $this->priceSharedCatalogManagement->expects($this->once())
            ->method('deleteProductTierPrices')->with($sharedCatalog, $productSkus)->willReturnSelf();
        $this->storage->expects($this->once())->method('getTierPrices')->willReturn($tierPrices);
        $this->scheduleBulk->expects($this->once())->method('execute')->with($sharedCatalog, $tierPrices);
    }

    /**
     * Test for method execute with exception.
     *
     * @return void
     */
    public function testExecuteWithException()
    {
        $configurationKey = 'configuration_key';
        $sharedCatalogId = 1;
        $storeId = 2;
        $exception = new \Exception('Exception Message');
        $this->request->expects($this->at(0))->method('getParam')->with('catalog_id')->willReturn($sharedCatalogId);
        $this->request->expects($this->at(1))
            ->method('getParam')->with('configure_key')->willReturn($configurationKey);
        $this->request->expects($this->at(2))->method('getParam')->with('store_id')->willReturn($storeId);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory
            ->expects($this->once())->method('create')->with(['key' => $configurationKey])->willReturn($storage);
        $this->configureCategory->expects($this->once())->method('saveConfiguredCategories')
            ->with($storage, $sharedCatalogId, $storeId)->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')->with($exception->getMessage())->willReturnSelf();
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())
            ->method('setPath')->with('shared_catalog/sharedCatalog/index')->willReturnSelf();
        $this->assertEquals($result, $this->save->execute());
    }

    /**
     * Test for method execute with InvalidArgumentException.
     *
     * @return void
     */
    public function testExecuteWithInvalidArgumentException()
    {
        $configurationKey = 'configuration_key';
        $sharedCatalogId = 1;
        $storeId = 2;
        $exception = new \InvalidArgumentException('Exception Message');
        $this->request->expects($this->at(0))->method('getParam')->with('catalog_id')->willReturn($sharedCatalogId);
        $this->request->expects($this->at(1))
            ->method('getParam')->with('configure_key')->willReturn($configurationKey);
        $this->request->expects($this->at(2))->method('getParam')->with('store_id')->willReturn($storeId);
        $storage = $this->getMockBuilder(\Magento\SharedCatalog\Model\Form\Storage\Wizard::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wizardStorageFactory
            ->expects($this->once())->method('create')->with(['key' => $configurationKey])->willReturn($storage);
        $this->configureCategory->expects($this->once())->method('saveConfiguredCategories')
            ->with($storage, $sharedCatalogId, $storeId)->willThrowException($exception);
        $this->logger->expects($this->once())->method('critical')->with($exception);
        $result = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectFactory->expects($this->once())->method('create')->willReturn($result);
        $result->expects($this->once())
            ->method('setPath')->with('shared_catalog/sharedCatalog/index')->willReturnSelf();
        $this->assertEquals($result, $this->save->execute());
    }
}
