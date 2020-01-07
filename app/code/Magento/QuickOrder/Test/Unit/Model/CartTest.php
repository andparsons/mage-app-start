<?php
namespace Magento\QuickOrder\Test\Unit\Model;

/**
 * Cart unit test.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\AdvancedCheckout\Model\Cart
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $localeFormatMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockItemMock;

    /**
     * @var \Magento\CatalogInventory\Model\StockState|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockState;

    /**
     * @var \Magento\Quote\Model\QuoteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteFactoryMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var \Magento\AdvancedCheckout\Model\IsProductInStockInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isInStock;

    /**
     * @var \Magento\QuickOrder\Model\CatalogPermissions\Permissions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $permissionsMock;

    /**
     * Set up.
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->helperMock = $this->getMockBuilder(\Magento\AdvancedCheckout\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->localeFormatMock = $this->getMockBuilder(\Magento\Framework\Locale\FormatInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->stockRegistry = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockRegistry::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStockItem', '__wakeup'])
            ->getMock();
        $this->stockItemMock = $this->getMockBuilder(\Magento\CatalogInventory\Model\Stock\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQtyIncrements', 'getIsInStock', '__wakeup', 'getMaxSaleQty', 'getMinSaleQty'])
            ->getMock();
        $this->stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->will($this->returnValue($this->stockItemMock));
        $this->stockState = $this->getMockBuilder(\Magento\CatalogInventory\Model\StockState::class)
            ->disableOriginalConstructor()
            ->setMethods(['checkQuoteItemQty'])
            ->getMock();
        $this->priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convertAndFormat'])
            ->getMockForAbstractClass();
        $this->isInStock = $this->getMockBuilder(\Magento\AdvancedCheckout\Model\IsProductInStock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $imageHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Image::class)
            ->disableOriginalConstructor()
            ->setMethods(['init', 'getUrl'])
            ->getMock();
        $imageHelper->expects($this->any())->method('init')->will($this->returnSelf());
        $imageHelper->expects($this->any())->method('getUrl')->will($this->returnValue('urlImage'));
        $this->quoteFactoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->permissionsMock = $this->getMockBuilder(\Magento\QuickOrder\Model\CatalogPermissions\Permissions::class)
            ->disableOriginalConstructor()
            ->setMethods(['isProductPermissionsValid'])
            ->getMock();
        $optionFactory = $this->getMockBuilder(\Magento\Catalog\Model\Product\OptionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistFactory = $this->getMockBuilder(\Magento\Wishlist\Model\WishlistFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\QuickOrder\Model\Cart::class,
            [
                'optionFactory' => $optionFactory,
                'wishlistFactory' => $wishlistFactory,
                'storeManager' => $this->storeManagerMock,
                'localeFormat' => $this->localeFormatMock,
                'stockRegistry' => $this->stockRegistry,
                'stockState' => $this->stockState,
                'productRepository' => $this->productRepository,
                'quoteFactory' => $this->quoteFactoryMock,
                'imageHelper' => $imageHelper,
                'permissions' => $this->permissionsMock,
                'priceCurrency' => $this->priceCurrency,
                'isProductInStock' => $this->isInStock
            ]
        );
        $this->model->setContext(\Magento\AdvancedCheckout\Model\Cart::CONTEXT_FRONTEND);
    }

    /**
     * Test for checkItem() method.
     *
     * @param string $sku
     * @param integer $qty
     * @param bool $isVisible
     * @param string $expectedCode
     * @param bool $isDisabled
     * @param bool $isProductPermissionsValid
     * @param bool $getDisableAddToCart
     * @param bool $isInStock
     * @param bool $isQtyHasError
     * @return void
     * @dataProvider checkItemDataProvider
     */
    public function testCheckItem(
        $sku,
        $qty,
        $isVisible,
        $expectedCode,
        $isDisabled,
        $isProductPermissionsValid,
        $getDisableAddToCart,
        $isInStock,
        $isQtyHasError
    ) {
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['getId', 'getWebsiteId', 'getMinSaleQty', 'getStore']
        );
        $storeMock->expects($this->any())->method('getStore')->will($this->returnValue(1));
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue(1));
        $storeMock->expects($this->any())->method('getMinSaleQty')->will($this->returnValue(1));

        $sessionMock = $this->createPartialMock(
            \Magento\Framework\Session\SessionManager::class,
            ['getAffectedItems', 'setAffectedItems']
        );
        $sessionMock->expects($this->any())->method('getAffectedItems')->will($this->returnValue([]));

        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            [
                'getId',
                'getWebsiteIds',
                'isComposite',
                '__wakeup',
                '__sleep',
                'getFinalPrice',
                'getStore',
                'getProductUrl',
                'getPreconfiguredValues',
                'isVisibleInSiteVisibility',
                'isDisabled',
                'getDisableAddToCart'
            ]
        );
        $productMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $productMock->expects($this->any())->method('getWebsiteIds')->will($this->returnValue([1]));
        $productMock->expects($this->any())->method('isComposite')->will($this->returnValue(false));
        if ($expectedCode !== \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_EMPTY) {
            $this->priceCurrency->expects($this->once())
                ->method('convertAndFormat');
        }
        $productMock->expects($this->any())->method('getFinalPrice')->will($this->returnValue(10));
        $productMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $productMock->expects($this->any())->method('getProductUrl')->will($this->returnValue('url'));
        $productMock->expects($this->any())->method('isVisibleInSiteVisibility')->willReturn($isVisible);
        $productMock->expects($this->any())->method('isDisabled')->willReturn($isDisabled);
        $this->permissionsMock->expects($this->any())->method('isProductPermissionsValid')
            ->willReturn($isProductPermissionsValid);
        $productMock->expects($this->any())->method('getDisableAddToCart')->willReturn($getDisableAddToCart);
        $preConfiguredValues = new \Magento\Framework\DataObject(['qty' => 1]);
        $productMock->expects($this->any())->method('getPreconfiguredValues')->willReturn($preConfiguredValues);

        $this->productRepository->expects($this->any())->method('get')->with($sku)
            ->will($this->returnValue($productMock));
        $this->helperMock->expects($this->any())->method('getSession')->will($this->returnValue($sessionMock));
        $this->localeFormatMock->expects($this->any())->method('getNumber')->will($this->returnArgument(0));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();
        $this->quoteFactoryMock ->expects($this->any())->method('create')->willReturn($quote);
        $quote->expects($this->any())->method('getStore')->willReturn($storeMock);
        $this->isInStock->expects($this->any())->method('execute')->willReturn($isInStock);
        $qtyStatusResult = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHasError'])
            ->getMock();
        $this->stockState->expects($this->any())->method('checkQuoteItemQty')->willReturn($qtyStatusResult);
        $qtyStatusResult->expects($this->any())->method('getHasError')->willReturn($isQtyHasError);
        $item = $this->model->checkItem($sku, $qty, ['test']);

        $this->assertTrue($item['code'] == $expectedCode);
    }

    /**
     * Data provider for testCheckItem.
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function checkItemDataProvider()
    {
        return [
            [
                'sku' => 'aaa',
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => true,
                'isQtyHasError' =>false
            ],
            [
                'sku' => 'aaa',
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU,
                'isDisabled' => true,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => false,
                'isQtyHasError' => false
            ],
            [
                'sku' => 'aaa',
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU,
                'isDisabled' => false,
                'isProductPermissionsValid' => false,
                'getDisableAddToCart' => false,
                'isInStock' => false,
                'isQtyHasError' => false
            ],
            [
                'sku' => 'aaa',
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_PERMISSIONS,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => true,
                'isInStock' => true,
                'isQtyHasError' =>false
            ],
            [
                'sku' => 'aaa',
                'qty' => 'aaa',
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => true,
                'isQtyHasError' =>false
            ],
            [
                'sku' => 'a',
                'qty' => 2,
                'isVisible' => false,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => true,
                'isQtyHasError' =>false
            ],
            [
                'sku' => 123,
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => true,
                'isQtyHasError' =>false
            ],
            [
                'sku' => 0,
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => true,
                'isQtyHasError' =>false
            ],
            [
                'sku' => '',
                'qty' => 2,
                'isVisible' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_EMPTY,
                'isDisabled' => false,
                'isProductPermissionsValid' => true,
                'getDisableAddToCart' => false,
                'isInStock' => false,
                'isQtyHasError' =>false
            ]
        ];
    }

    /**
     * Test for prepareAddProductBySku() method.
     *
     * @param int|null $productId
     * @param int $qty
     * @param bool $isVisible
     * @param bool $isComposite
     * @param bool $isInStock
     * @param array $affectedItems
     * @param bool|array $qtyStatus
     * @param string $expectedCode
     * @param integer $expectedError
     * @param integer $expectedQty
     * @return void
     * @dataProvider prepareAddProductsBySkuDataProvider
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function te1stPrepareAddProductBySku(
        $productId,
        $qty,
        $isVisible,
        $isComposite,
        $isInStock,
        array $affectedItems,
        $qtyStatus,
        $expectedCode,
        $expectedError,
        $expectedQty
    ) {
        $this->permissionsMock->expects($this->any())->method('isProductPermissionsValid')
            ->willReturn(true);
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['getId', 'getWebsiteId', 'getMinSaleQty', 'getStore']
        );
        $storeMock->expects($this->any())->method('getStore')->will($this->returnValue(1));
        $storeMock->expects($this->any())->method('getWebsiteId')->will($this->returnValue(1));
        $storeMock->expects($this->any())->method('getMinSaleQty')->will($this->returnValue(1));

        $sessionMock = $this->createPartialMock(
            \Magento\Framework\Session\SessionManager::class,
            ['getAffectedItems', 'setAffectedItems']
        );
        $sessionMock->expects($this->any())->method('getAffectedItems')->will($this->returnValue($affectedItems));

        $productMock = $this->createPartialMock(
            Magento\Catalog\Model\Product::class,
            [
                'getId',
                'getWebsiteIds',
                'isComposite',
                'getFinalPrice',
                'getStore',
                'getProductUrl',
                'getPreconfiguredValues',
                'isVisibleInSiteVisibility'
            ]
        );
        $productMock->expects($this->any())->method('getId')->willReturn($productId);
        $productMock->expects($this->any())->method('getWebsiteIds')->will($this->returnValue([1]));
        $productMock->expects($this->any())->method('isComposite')->willReturn($isComposite);
        $productMock->expects($this->any())->method('getFinalPrice')->will($this->returnValue(10));
        $productMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));
        $productMock->expects($this->any())->method('getProductUrl')->will($this->returnValue('url'));
        $productMock->expects($this->any())->method('isVisibleInSiteVisibility')->willReturn($isVisible);
        $preConfiguredValues = new \Magento\Framework\DataObject(['qty' => 1]);
        $productMock->expects($this->any())->method('getPreconfiguredValues')->willReturn($preConfiguredValues);

        $this->productRepository->expects($this->any())->method('get')->will($this->returnValue($productMock));
        $this->helperMock->expects($this->any())->method('getSession')->will($this->returnValue($sessionMock));
        $this->helperMock->expects($this->any())->method('getMessage')
            ->will($this->returnArgument(0));
        $this->localeFormatMock->expects($this->any())->method('getNumber')->will($this->returnArgument(0));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $this->model->expects($this->any())->method('getQtyStatus')->willReturn($qtyStatus);
        $this->model->expects($this->any())->method('_isProductOutOfStock')->willReturn(!$isInStock);

        $this->model->setAffectedItems($affectedItems);

        $item = $this->model->prepareAddProductBySku('aaa', $qty, ['test']);

        $this->assertTrue(
            $item['result'] == $expectedCode
            && $item['isError'] == $expectedError
            && $item['qty'] == $expectedQty
        );
    }

    /**
     * Data provider prepareAddProductsBySku.
     *
     * @return array
     */
    public function prepareAddProductsBySkuDataProvider()
    {
        return [
            [
                'productId' => 1,
                'qty' => 2,
                'isVisible' => true,
                'isComposite' => false,
                'isInStock' => true,
                'affectedItems' => [
                    'aaa' => [
                        'qty' => 2,
                    ],
                ],
                'qtyStatus' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
                'expectedError' => 0,
                'expectedQty' => 4,
            ],
            [
                'productId' => 1,
                'qty' => 0,
                'isVisible' => false,
                'isComposite' => false,
                'isInStock' => true,
                'affectedItems' => [
                    'aaa' => [
                        'qty' => 2,
                    ],
                ],
                'qtyStatus' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_SUCCESS,
                'expectedError' => 0,
                'expectedQty' => 3,
            ],
            [
                'productId' => 1,
                'qty' => 2,
                'isVisible' => true,
                'isComposite' => true,
                'isInStock' => true,
                'affectedItems' => [],
                'qtyStatus' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_CONFIGURE,
                'expectedError' => 0,
                'expectedQty' => 2,
            ],
            [
                'productId' => null,
                'qty' => 2,
                'isVisible' => true,
                'isComposite' => true,
                'isInStock' => true,
                'affectedItems' => [
                    'aaa' => [
                        'qty' => 1,
                    ],
                ],
                'qtyStatus' => true,
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_SKU,
                'expectedError' => 1,
                'expectedQty' => 0,
            ],
            [
                'productId' => 1,
                'qty' => 2,
                'isVisible' => true,
                'isComposite' => false,
                'isInStock' => true,
                'affectedItems' => [],
                'qtyStatus' => [
                    'status' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED_IN_CART,
                    'qty_max_allowed' => 1,
                ],
                'expectedCode' => \Magento\AdvancedCheckout\Helper\Data::ADD_ITEM_STATUS_FAILED_QTY_ALLOWED_IN_CART,
                'expectedError' => 1,
                'expectedQty' => 2,
            ],
            [
                'productId' => 1,
                'qty' => 2,
                'isVisible' => false,
                'isComposite' => false,
                'isInStock' => false,
                'affectedItems' => [],
                'qtyStatus' => true,
                'expectedCode' => __('The SKU is out of stock.'),
                'expectedError' => 1,
                'expectedQty' => 2,
            ],
        ];
    }
}
