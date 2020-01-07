<?php
namespace Magento\SharedCatalog\Test\Unit\Plugin\Quote\Api;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for ValidateAddProductToCartPlugin.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateAddProductToCartPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SharedCatalog\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteRepository;

    /**
     * @var \Magento\SharedCatalog\Model\SharedCatalogLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogLocator;

    /**
     * @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogManagement;

    /**
     * @var \Magento\SharedCatalog\Api\ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sharedCatalogProductManagement;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\SharedCatalog\Plugin\Quote\Api\ValidateAddProductToCartPlugin
     */
    private $validateAddProductToCartPlugin;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->moduleConfig = $this->getMockBuilder(\Magento\SharedCatalog\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepository = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogLocator = $this->getMockBuilder(\Magento\SharedCatalog\Model\SharedCatalogLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sharedCatalogManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->sharedCatalogProductManagement = $this
            ->getMockBuilder(\Magento\SharedCatalog\Api\ProductManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->validateAddProductToCartPlugin = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Plugin\Quote\Api\ValidateAddProductToCartPlugin::class,
            [
                'moduleConfig' => $this->moduleConfig,
                'quoteRepository' => $this->quoteRepository,
                'sharedCatalogLocator' => $this->sharedCatalogLocator,
                'sharedCatalogManagement' => $this->sharedCatalogManagement,
                'sharedCatalogProductManagement' => $this->sharedCatalogProductManagement,
                'storeManager' => $this->storeManager,
            ]
        );
    }

    /**
     * Test for beforeSave().
     *
     * @param int $customerGroupId
     * @param int $getSharedCatalogByCustomerGroupInvokesCount
     * @param int $getPublicCatalogInvokesCount
     * @return void
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave(
        $customerGroupId,
        $getSharedCatalogByCustomerGroupInvokesCount,
        $getPublicCatalogInvokesCount
    ) {
        $quoteId = 2;
        $productSku = 'sku';
        $sharedCatalogId = 3;
        $sharedCatalogProductsSkus = ['sku', 'sku1'];
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItem->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId)->willReturn($quote);
        $cartItem->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $sharedCatalog = $this->getMockBuilder(\Magento\SharedCatalog\Api\Data\SharedCatalogInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $sharedCatalog->expects($this->atLeastOnce())->method('getId')->willReturn($sharedCatalogId);
        $this->sharedCatalogLocator->expects($this->exactly($getSharedCatalogByCustomerGroupInvokesCount))
            ->method('getSharedCatalogByCustomerGroup')->with($customerGroupId)->willReturn($sharedCatalog);
        $this->sharedCatalogManagement->expects($this->exactly($getPublicCatalogInvokesCount))
            ->method('getPublicCatalog')->willReturn($sharedCatalog);
        $this->sharedCatalogProductManagement->expects($this->atLeastOnce())->method('getProducts')
            ->with($sharedCatalogId)->willReturn($sharedCatalogProductsSkus);
        $cartItemRepository = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result = $this->validateAddProductToCartPlugin->beforeSave($cartItemRepository, $cartItem);

        $this->assertInstanceOf(\Magento\Quote\Api\Data\CartItemInterface::class, $result[0]);
    }

    /**
     * Test for beforeSave() with NoSuchEntityException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testBeforeSaveWithNoSuchEntityException()
    {
        $customerGroupId = 1;
        $quoteId = 2;
        $productSku = 'sku';
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManager->expects($this->atLeastOnce())->method('getWebsite')->willReturn($website);
        $this->moduleConfig->expects($this->atLeastOnce())->method('isActive')->willReturn(true);
        $quote = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId'])
            ->getMockForAbstractClass();
        $quote->expects($this->atLeastOnce())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $cartItem = $this->getMockBuilder(\Magento\Quote\Api\Data\CartItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $cartItem->expects($this->atLeastOnce())->method('getQuoteId')->willReturn($quoteId);
        $this->quoteRepository->expects($this->atLeastOnce())->method('get')->with($quoteId)->willReturn($quote);
        $cartItem->expects($this->atLeastOnce())->method('getSku')->willReturn($productSku);
        $this->sharedCatalogLocator->expects($this->atLeastOnce())->method('getSharedCatalogByCustomerGroup')
            ->with($customerGroupId)->willReturn(null);
        $cartItemRepository = $this->getMockBuilder(\Magento\Quote\Api\CartItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->validateAddProductToCartPlugin->beforeSave($cartItemRepository, $cartItem);
    }

    /**
     * DataProvider beforeSave().
     *
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            [1, 1, 0],
            [0, 0, 1]
        ];
    }
}
