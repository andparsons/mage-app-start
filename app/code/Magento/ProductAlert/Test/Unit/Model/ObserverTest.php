<?php
namespace Magento\ProductAlert\Test\Unit\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ProductAlert\Model\ProductSalability;

/**
 * Class ObserverTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\ProductAlert\Model\Observer
     */
    private $observer;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transportBuilderMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inlineTranslationMock;

    /**
     * @var \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapCollectionMock;

    /**
     * @var \Magento\Sitemap\Model\Sitemap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sitemapMock;

    /**
     * @var \Magento\ProductAlert\Model\EmailFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailFactoryMock;

    /**
     * @var \Magento\ProductAlert\Model\Email|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emailMock;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceColFactoryMock;

    /**
     * @var \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockColFactoryMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var ProductSalability|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productSalabilityMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->transportBuilderMock = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(\Magento\Framework\Translate\Inline\StateInterface::class)
            ->getMock();
        $this->sitemapCollectionMock = $this->createPartialMock(
            \Magento\Sitemap\Model\ResourceModel\Sitemap\Collection::class,
            ['getIterator']
        );
        $this->sitemapMock = $this->createPartialMock(\Magento\Sitemap\Model\Sitemap::class, ['generateXml']);

        $this->emailFactoryMock = $this->getMockBuilder(
            \Magento\ProductAlert\Model\EmailFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->emailMock = $this->getMockBuilder(\Magento\ProductAlert\Model\Email::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceColFactoryMock = $this->getMockBuilder(
            \Magento\ProductAlert\Model\ResourceModel\Price\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create', 'addWebsiteFilter', 'setCustomerOrder'])
            ->getMock();
        $this->stockColFactoryMock = $this->getMockBuilder(
            \Magento\ProductAlert\Model\ResourceModel\Stock\CollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create', 'addWebsiteFilter', 'setCustomerOrder', 'addStatusFilter'])
            ->getMock();

        $this->websiteMock = $this->createPartialMock(
            \Magento\Store\Model\Website::class,
            ['getDefaultGroup', 'getDefaultStore']
        );
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultStore', 'getId'])
            ->getMock();
        $this->customerRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\CustomerRepositoryInterface::class)
            ->getMock();
        $this->productRepositoryMock = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->getMock();
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setCustomerGroupId',
                    'getFinalPrice',
                ]
            )->getMock();

        $this->productSalabilityMock = $this->createPartialMock(ProductSalability::class, ['isSalable']);

        $this->objectManager = new ObjectManager($this);
        $this->observer = $this->objectManager->getObject(
            \Magento\ProductAlert\Model\Observer::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'transportBuilder' => $this->transportBuilderMock,
                'inlineTranslation' => $this->inlineTranslationMock,
                'emailFactory' => $this->emailFactoryMock,
                'priceColFactory' => $this->priceColFactoryMock,
                'stockColFactory' => $this->stockColFactoryMock,
                'customerRepository' => $this->customerRepositoryMock,
                'productRepository' => $this->productRepositoryMock,
                'productSalability' => $this->productSalabilityMock
            ]
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWebsitesThrowsException()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willThrowException(new \Exception());

        $this->observer->process();
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessPriceThrowsException()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(true);

        $this->priceColFactoryMock->expects($this->once())->method('create')->willThrowException(new \Exception());

        $this->observer->process();
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessPriceCustomerRepositoryThrowsException()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(true);

        $this->priceColFactoryMock->expects($this->once())->method('create')->willReturnSelf();
        $this->priceColFactoryMock->expects($this->once())->method('addWebsiteFilter')->willReturnSelf();
        $items = [
            new \Magento\Framework\DataObject([
                'customer_id' => '42'
            ])
        ];

        $this->priceColFactoryMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturn(new \ArrayIterator($items));

        $this->customerRepositoryMock->expects($this->once())->method('getById')->willThrowException(new \Exception());

        $this->observer->process();
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessPriceEmailThrowsException()
    {
        $id = 1;
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();
        $this->websiteMock->expects($this->once())->method('getDefaultStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getId')->willReturn(2);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(true);

        $this->priceColFactoryMock->expects($this->once())->method('create')->willReturnSelf();
        $this->priceColFactoryMock->expects($this->once())->method('addWebsiteFilter')->willReturnSelf();

        $items = [
            new \Magento\Framework\DataObject([
                'customer_id' => $id
            ])
        ];
        $this->priceColFactoryMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturn(new \ArrayIterator($items));

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customerMock);

        $this->productMock->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->productMock->expects($this->once())->method('getFinalPrice')->willReturn('655.99');
        $this->productRepositoryMock->expects($this->once())->method('getById')->willReturn($this->productMock);

        $this->emailMock->expects($this->once())->method('send')->willThrowException(new \Exception());

        $this->observer->process();
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessStockThrowsException()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))->method('getValue')->willReturn(false);
        $this->scopeConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $this->stockColFactoryMock->expects($this->once())->method('create')->willThrowException(new \Exception());

        $this->observer->process();
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessStockCustomerRepositoryThrowsException()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();

        $this->scopeConfigMock->expects($this->at(0))->method('getValue')->willReturn(false);
        $this->scopeConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $this->stockColFactoryMock->expects($this->once())->method('create')->willReturnSelf();
        $this->stockColFactoryMock->expects($this->once())->method('addWebsiteFilter')->willReturnSelf();
        $this->stockColFactoryMock->expects($this->once())->method('addStatusFilter')->willReturnSelf();
        $items = [
            new \Magento\Framework\DataObject([
                'customer_id' => '42'
            ])
        ];

        $this->stockColFactoryMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturn(new \ArrayIterator($items));

        $this->customerRepositoryMock->expects($this->once())->method('getById')->willThrowException(new \Exception());

        $this->observer->process();
    }

    /**
     * @expectedException \Exception
     */
    public function testProcessStockEmailThrowsException()
    {
        $this->scopeConfigMock->expects($this->any())->method('isSetFlag')->willReturn(false);

        $this->emailFactoryMock->expects($this->once())->method('create')->willReturn($this->emailMock);

        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn([$this->websiteMock]);
        $this->websiteMock->expects($this->any())->method('getDefaultGroup')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getDefaultStore')->willReturnSelf();
        $this->websiteMock->expects($this->once())->method('getDefaultStore')->willReturn($this->storeMock);
        $this->storeMock->expects($this->any())->method('getId')->willReturn(2);

        $this->scopeConfigMock->expects($this->at(0))->method('getValue')->willReturn(false);
        $this->scopeConfigMock->expects($this->at(1))->method('getValue')->willReturn(true);

        $this->stockColFactoryMock->expects($this->once())->method('create')->willReturnSelf();
        $this->stockColFactoryMock->expects($this->once())->method('addWebsiteFilter')->willReturnSelf();
        $this->stockColFactoryMock->expects($this->once())->method('addStatusFilter')->willReturnSelf();
        $items = [
            new \Magento\Framework\DataObject([
                'customer_id' => '42'
            ])
        ];

        $this->stockColFactoryMock->expects($this->once())
            ->method('setCustomerOrder')
            ->willReturn(new \ArrayIterator($items));

        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->customerRepositoryMock->expects($this->once())->method('getById')->willReturn($customerMock);

        $this->productMock->expects($this->once())->method('setCustomerGroupId')->willReturnSelf();
        $this->productSalabilityMock->expects($this->once())->method('isSalable')->willReturn(false);
        $this->productRepositoryMock->expects($this->once())->method('getById')->willReturn($this->productMock);

        $this->emailMock->expects($this->once())->method('send')->willThrowException(new \Exception());

        $this->observer->process();
    }
}
