<?php

namespace Magento\SharedCatalog\Test\Unit\Ui\DataProvider\Modifier;

use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;

/**
 * Test for Magento\SharedCatalog\Ui\DataProvider\Modifier\TierPrice.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Locale\CurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productPriceOptions;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\SharedCatalog\Ui\DataProvider\Modifier\TierPrice
     */
    private $tierPrice;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->localeCurrency = $this->getMockBuilder(\Magento\Framework\Locale\CurrencyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productPriceOptions = $this
            ->getMockBuilder(\Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->sharedCatalogRepository = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->productRepository = $this
            ->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->scopeConfig = $this
            ->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPrice = $objectManager->getObject(
            \Magento\SharedCatalog\Ui\DataProvider\Modifier\TierPrice::class,
            [
                'localeCurrency' => $this->localeCurrency,
                'storeManager' => $this->storeManager,
                'productPriceOptions' => $this->productPriceOptions,
                'request' => $this->request,
                'sharedCatalogRepository' => $this->sharedCatalogRepository,
                'productRepository' => $this->productRepository,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    /**
     * Test for modifyMeta method.
     *
     * @return void
     */
    public function testModifyMeta()
    {
        $meta = ['meta_key1' => 'meta_value1'];
        $sharedCatalogId = 1;
        $storeId = 0;
        $websiteId = 2;
        $productId = 3;
        $baseCurrencyCode = 'USD';
        $currencySymbol = '$';
        $websiteName = 'Website 1';
        $websiteOptions = [
            [
                'label' => __('All Websites') . ' [' . $baseCurrencyCode . ']',
                'value' => 0,
            ],
            [
                'label' => $websiteName . '[' . $baseCurrencyCode . ']',
                'value' => $websiteId,
            ]
        ];
        $priceOptions = ['option_key1' => 'option_value1'];
        $this->storeManager->expects($this->atLeastOnce())->method('isSingleStoreMode')->willReturn(false);
        $this->scopeConfig->expects($this->exactly(2))->method('getValue')
            ->withConsecutive(
                ['currency/options/base', 'default'],
                ['catalog/price/scope', 'store']
            )->willReturnOnConsecutiveCalls($baseCurrencyCode, \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM],
                ['store_id'],
                ['product_id']
            )->willReturnOnConsecutiveCalls($sharedCatalogId, $storeId, $productId);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $sharedCatalog->expects($this->atLeastOnce())->method('getStoreId')->willReturn(null);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('get')->with($sharedCatalogId)->willReturn($sharedCatalog);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->with($storeId)->willReturn($store);
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn($websiteId);
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->setMethods(['getCurrencySymbol'])
            ->disableOriginalConstructor()->getMock();
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->setMethods(['getBaseCurrencyCode'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->once())->method('getWebsites')->willReturn([$website]);
        $product = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->disableOriginalConstructor()->getMock();
        $productExtensionAttributes = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getWebsiteIds'])
            ->getMockForAbstractClass();
        $this->productRepository->expects($this->once())->method('getById')->willReturn($product);
        $product->expects($this->once())->method('getExtensionAttributes')->willReturn($productExtensionAttributes);
        $productExtensionAttributes->expects($this->once())->method('getWebsiteIds')->willReturn([$websiteId]);
        $website->expects($this->once())->method('getName')->willReturn($websiteName);
        $website->expects($this->atLeastOnce())->method('getId')->willReturn($websiteId);
        $website->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $currency->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);
        $this->productPriceOptions->expects($this->once())->method('toOptionArray')->willReturn($priceOptions);
        $tierPriceStructure = $this->getTierPriceStructure($websiteOptions, $websiteId, $currencySymbol, $priceOptions);
        $this->assertEquals(
            array_merge($meta, ['tier_price_fs' => ['children' => ['tier_price' => $tierPriceStructure]]]),
            $this->tierPrice->modifyMeta($meta)
        );
    }

    /**
     * Test for modifyMeta method with not applicable website.
     *
     * @return void
     */
    public function testModifyMetaWithNotApplicableWebsite()
    {
        $meta = ['meta_key1' => 'meta_value1'];
        $sharedCatalogId = 1;
        $storeId = 0;
        $websiteId = 2;
        $productId = 3;
        $baseCurrencyCode = 'USD';
        $currencySymbol = '$';
        $websiteOptions = [
            [
                'label' => __('All Websites') . ' [' . $baseCurrencyCode . ']',
                'value' => 0,
            ]
        ];
        $priceOptions = ['option_key1' => 'option_value1'];
        $this->storeManager->expects($this->atLeastOnce())->method('isSingleStoreMode')->willReturn(false);
        $this->scopeConfig->expects($this->exactly(2))->method('getValue')
            ->withConsecutive(
                ['currency/options/base', 'default'],
                ['catalog/price/scope', 'store']
            )->willReturnOnConsecutiveCalls($baseCurrencyCode, \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM],
                ['store_id'],
                ['product_id']
            )->willReturnOnConsecutiveCalls($sharedCatalogId, $storeId, $productId);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $sharedCatalog->expects($this->atLeastOnce())->method('getStoreId')->willReturn(null);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('get')->with($sharedCatalogId)->willReturn($sharedCatalog);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->with($storeId)->willReturn($store);
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn($websiteId);
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->setMethods(['getCurrencySymbol'])
            ->disableOriginalConstructor()->getMock();
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->setMethods(['getBaseCurrencyCode'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->once())->method('getWebsites')->willReturn([$website]);
        $this->productRepository->expects($this->once())->method('getById')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $currency->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);
        $this->productPriceOptions->expects($this->once())->method('toOptionArray')->willReturn($priceOptions);
        $tierPriceStructure = $this->getTierPriceStructure($websiteOptions, $websiteId, $currencySymbol, $priceOptions);
        $this->assertEquals(
            array_merge($meta, ['tier_price_fs' => ['children' => ['tier_price' => $tierPriceStructure]]]),
            $this->tierPrice->modifyMeta($meta)
        );
    }

    /**
     * Test for modifyMeta method with selected website.
     *
     * @return void
     */
    public function testModifyMetaWithSelectedWebsite()
    {
        $meta = ['meta_key1' => 'meta_value1'];
        $sharedCatalogId = 1;
        $storeId = 2;
        $websiteId = 3;
        $baseCurrencyCode = 'USD';
        $currencySymbol = '$';
        $websiteName = 'Website 1';
        $websiteOptions = [
            [
                'label' => $websiteName . '[' . $baseCurrencyCode . ']',
                'value' => $websiteId,
            ]
        ];
        $priceOptions = ['option_key1' => 'option_value1'];
        $this->storeManager->expects($this->exactly(2))->method('isSingleStoreMode')->willReturn(false);
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with('catalog/price/scope', 'store')->willReturn(\Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM],
                ['store_id'],
                ['store_id'],
                ['store_id'],
                ['store_id']
            )->willReturnOnConsecutiveCalls($sharedCatalogId, $storeId, $storeId, $storeId, $storeId);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $sharedCatalog->expects($this->atLeastOnce())->method('getStoreId')->willReturn(null);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('get')->with($sharedCatalogId)->willReturn($sharedCatalog);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->with($storeId)->willReturn($store);
        $store->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn($websiteId);
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->setMethods(['getCurrencySymbol'])
            ->disableOriginalConstructor()->getMock();
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->setMethods(['getBaseCurrencyCode'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->once())->method('getWebsite')->with($websiteId)->willReturn($website);
        $website->expects($this->once())->method('getName')->willReturn($websiteName);
        $website->expects($this->once())->method('getId')->willReturn($websiteId);
        $website->expects($this->once())->method('getBaseCurrencyCode')->willReturn($baseCurrencyCode);
        $currency->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);
        $this->productPriceOptions->expects($this->once())->method('toOptionArray')->willReturn($priceOptions);
        $tierPriceStructure = $this->getTierPriceStructure($websiteOptions, $websiteId, $currencySymbol, $priceOptions);
        $this->assertEquals(
            array_merge($meta, ['tier_price_fs' => ['children' => ['tier_price' => $tierPriceStructure]]]),
            $this->tierPrice->modifyMeta($meta)
        );
    }

    /**
     * Test for modifyMeta method in single store mode.
     *
     * @return void
     */
    public function testModifyMetaInSingleStoreMode()
    {
        $meta = ['meta_key1' => 'meta_value1'];
        $sharedCatalogId = 1;
        $storeId = 2;
        $baseCurrencyCode = 'USD';
        $currencySymbol = '$';
        $websiteOptions = [
            [
                'label' => __('All Websites') . ' [' . $baseCurrencyCode . ']',
                'value' => 0,
            ]
        ];
        $priceOptions = ['option_key1' => 'option_value1'];
        $this->storeManager->expects($this->once())->method('isSingleStoreMode')->willReturn(true);
        $this->scopeConfig->expects($this->exactly(2))->method('getValue')
            ->withConsecutive(
                ['currency/options/base', 'default'],
                ['catalog/price/scope', 'store']
            )->willReturnOnConsecutiveCalls($baseCurrencyCode, \Magento\Store\Model\Store::PRICE_SCOPE_GLOBAL);
        $this->request->expects($this->atLeastOnce())->method('getParam')
            ->withConsecutive(
                [SharedCatalogInterface::SHARED_CATALOG_ID_URL_PARAM],
                ['store_id'],
                ['store_id']
            )->willReturnOnConsecutiveCalls($sharedCatalogId, $storeId, $storeId);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $sharedCatalog->expects($this->atLeastOnce())->method('getStoreId')->willReturn(null);
        $this->sharedCatalogRepository->expects($this->once())
            ->method('get')->with($sharedCatalogId)->willReturn($sharedCatalog);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->setMethods(['getBaseCurrency'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->once())->method('getStore')->with($storeId)->willReturn($store);
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->setMethods(['getCurrencySymbol'])
            ->disableOriginalConstructor()->getMock();
        $store->expects($this->once())->method('getBaseCurrency')->willReturn($currency);
        $currency->expects($this->once())->method('getCurrencySymbol')->willReturn($currencySymbol);
        $this->productPriceOptions->expects($this->once())->method('toOptionArray')->willReturn($priceOptions);
        $tierPriceStructure = $this->getTierPriceStructure($websiteOptions, 0, $currencySymbol, $priceOptions);
        $this->assertEquals(
            array_merge($meta, ['tier_price_fs' => ['children' => ['tier_price' => $tierPriceStructure]]]),
            $this->tierPrice->modifyMeta($meta)
        );
    }

    /**
     * Get tier price dynamic rows structure.
     *
     * @param array $websiteOptions
     * @param int $defaultWebsiteId
     * @param string $currencySymbol
     * @param array $priceOptions
     * @return array
     */
    private function getTierPriceStructure(
        array $websiteOptions,
        $defaultWebsiteId,
        $currencySymbol,
        array $priceOptions
    ) {
        return [
            'children' => [
                'record' => [
                    'children' => [
                        'website_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'options' => $websiteOptions,
                                        'value' => $defaultWebsiteId,
                                        'visible' => true,
                                        'disabled' => false,
                                    ],
                                ],
                            ],
                        ],
                        'price_value' => [
                            'children' => [
                                'price' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'addbefore' => $currencySymbol,
                                            ]
                                        ]
                                    ],
                                ],
                                'value_type' => [
                                    'arguments' => [
                                        'data' => [
                                            'options' => $priceOptions,
                                        ]
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test for modifyData method.
     *
     * @return void
     */
    public function testModifyData()
    {
        $data = ['data_key1' => 'data_value1'];
        $productId = 1;
        $websiteId = 2;
        $baseCurrencyCode = 'USD';
        $websiteCurrencyCode = 'EUR';
        $this->request->expects($this->once())->method('getParam')->with('product_id')->willReturn($productId);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')->with('currency/options/base', 'default')->willReturn($baseCurrencyCode);
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->setMethods(['getBaseCurrencyCode'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->storeManager->expects($this->once())->method('getWebsites')->willReturn([$website]);
        $website->expects($this->once())->method('getId')->willReturn($websiteId);
        $website->expects($this->once())->method('getBaseCurrencyCode')->willReturn($websiteCurrencyCode);
        $currency = $this->getMockBuilder(\Magento\Framework\Currency::class)
            ->disableOriginalConstructor()->getMock();
        $this->localeCurrency->expects($this->exactly(2))->method('getCurrency')
            ->withConsecutive([$baseCurrencyCode], [$websiteCurrencyCode])->willReturn($currency);
        $currency->expects($this->exactly(2))->method('getSymbol')->willReturnOnConsecutiveCalls('$', '€');
        $this->assertEquals(
            $data +
            [
                $productId => [
                    'base_currencies' => [
                        ['website_id' => 0, 'symbol' => '$'],
                        ['website_id' => $websiteId, 'symbol' => '€'],
                    ]
                ]
            ],
            $this->tierPrice->modifyData($data)
        );
    }
}
