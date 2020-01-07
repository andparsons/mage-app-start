<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Configure;

/**
 * Unit tests for \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StepDataProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websitesDataProvider;

    /**
     * @var \Magento\Ui\DataProvider\Modifier\PoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $modifiers;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\SharedCatalog\Model\ProductItemTierPriceValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productItemTierPriceValidator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepositoryMock;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeCurrency;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor
     */
    private $dataProvider;

    /**
     * Set up for test.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->websitesDataProvider = $this->getMockBuilder(\Magento\SharedCatalog\Ui\DataProvider\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->modifiers = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\PoolInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore', 'setCurrentStore', 'getWebsite'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productItemTierPriceValidator = $this->getMockBuilder(
            \Magento\SharedCatalog\Model\ProductItemTierPriceValidator::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogRepositoryMock = $this->getMockBuilder(
            \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->localeCurrency = $this->getMockBuilder(\Magento\Framework\Locale\CurrencyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dataProvider = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Configure\StepDataProcessor::class,
            [
                'request' => $this->request,
                'modifiers' => $this->modifiers,
                'storeManager' => $this->storeManager,
                'websitesDataProvider' => $this->websitesDataProvider,
                'scopeConfig' => $this->scopeConfig,
                'productItemTierPriceValidator' => $this->productItemTierPriceValidator,
                'sharedCatalogRepository' => $this->sharedCatalogRepositoryMock,
                'localeCurrency' => $this->localeCurrency,
            ]
        );
    }

    /**
     * Test for modifyData method.
     *
     * @return void
     */
    public function testModifyData()
    {
        $data = ['test_data'];
        $expectedResult = ['test_data_modified'];
        $modifier = $this->getMockBuilder(\Magento\Ui\DataProvider\Modifier\ModifierInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->modifiers->expects($this->once())->method('getModifiersInstances')->willReturn([$modifier]);
        $modifier->expects($this->once())->method('modifyData')->with($data)->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->dataProvider->modifyData($data));
    }

    /**
     * Test for switchCurrentStore method.
     *
     * @return void
     */
    public function testSwitchCurrentStore()
    {
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->atLeastOnce())->method('getCode')->willReturn('test_code');
        $this->request->expects($this->once())->method('getParam')->with('filters')->willReturn(['websites' => 1]);
        $this->websitesDataProvider->expects($this->once())->method('getStoreByWebsiteId')->with(1)->willReturn($store);
        $this->storeManager->expects($this->once())->method('setCurrentStore')->with('test_code');
        $this->dataProvider->switchCurrentStore();
    }

    /**
     * Test for isCustomPriceEnabled method.
     *
     * @return void
     */
    public function testGetWebsites()
    {
        $websites = [
            [
                'value' => 0,
                'label' => __('All Websites')
            ],
            [
                'value' => 3,
                'label' => __('Website 3')
            ]
        ];
        $result = [
            'items' => $websites,
            'selected' => 3,
            'isPriceScopeGlobal' => true,
            'currencySymbol' => '$'
        ];
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseCurrencyCode'])
            ->getMockForAbstractClass();
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websitesDataProvider->expects($this->once())->method('getWebsites')->willReturn($websites);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(\Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL);
        $this->request->expects($this->once())->method('getParam')->with('filters')->willReturn(['websites' => 3]);
        $this->storeManager->expects($this->once())->method('getWebsite')->with(3)->willReturn($website);
        $website->expects($this->once())->method('getBaseCurrencyCode')->willReturn('USD');
        $this->localeCurrency->expects($this->once())->method('getCurrency')->with('USD')->willReturn($currency);
        $currency->expects($this->once())->method('getSymbol')->willReturn('$');

        $this->assertEquals($result, $this->dataProvider->getWebsites());
    }

    /**
     * Test for isCustomPriceEnabled method.
     *
     * @param bool $result
     * @param array $customPrices
     * @return void
     * @dataProvider isCustomPriceEnabledDataProvider
     */
    public function testIsCustomPriceEnabled($result, array $customPrices)
    {
        $websiteId = 2;
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('filters')
            ->willReturn(['websites' => $websiteId]);
        $this->productItemTierPriceValidator->expects($this->once())
            ->method('canChangePrice')
            ->with($customPrices, $websiteId)
            ->willReturn($result);
        $this->assertEquals($result, $this->dataProvider->isCustomPriceEnabled($customPrices));
    }

    /**
     * Test for retrieveSharedCatalogWebsiteIds() method.
     *
     * @return void
     */
    public function testRetrieveSharedCatalogWebsiteIds()
    {
        $sharedCatalogId = 1;
        $expectedWebsiteId = 1;

        $sharedCatalogMock = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedCatalogMock->expects($this->once())->method('getStoreId')->willReturn(null);
        $this->storeManager->expects($this->once())->method('getWebsites')->willReturn([1 => 'test']);
        $this->sharedCatalogRepositoryMock->expects($this->once())->method('get')->with($sharedCatalogId)
            ->willReturn($sharedCatalogMock);

        $this->assertEquals(
            [$expectedWebsiteId],
            $this->dataProvider->retrieveSharedCatalogWebsiteIds($sharedCatalogId)
        );
    }

    /**
     * Test for retrieveSharedCatalogWebsiteIds() method when shared catalog has store ID.
     *
     * @return void
     */
    public function testRetrieveSharedCatalogWebsiteIdsHasStoreId()
    {
        $sharedCatalogId = 1;
        $expectedWebsiteId = 1;
        $defaultStoreId = 1;

        $sharedCatalogMock = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sharedCatalogMock->expects($this->once())->method('getStoreId')->willReturn($defaultStoreId);
        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->with($defaultStoreId)
            ->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getWebsiteId')->willReturn($expectedWebsiteId);
        $this->sharedCatalogRepositoryMock->expects($this->once())->method('get')->with($sharedCatalogId)
            ->willReturn($sharedCatalogMock);

        $this->assertEquals(
            [$expectedWebsiteId],
            $this->dataProvider->retrieveSharedCatalogWebsiteIds($sharedCatalogId)
        );
    }

    /**
     * Test prepareCustomPrice method.
     *
     * @return void
     */
    public function testPrepareCustomPrice()
    {
        $websiteId = 0;
        $customPricesData = [
            'custom_prices_data_0',
            'custom_prices_data_1'
        ];
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('filters')
            ->willReturn(['websites' => $websiteId]);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(\Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE);
        $this->assertNull($this->dataProvider->prepareCustomPrice($customPricesData));
    }

    /**
     * Data provider for getData method.
     *
     * @return array
     */
    public function isCustomPriceEnabledDataProvider()
    {
        return [
            [
                false,
                [1, 2]
            ],
            [
                true,
                [1]
            ],
        ];
    }
}
