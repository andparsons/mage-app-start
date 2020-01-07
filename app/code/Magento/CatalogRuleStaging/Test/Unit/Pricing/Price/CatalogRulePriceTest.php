<?php

namespace Magento\CatalogRuleStaging\Test\Unit\Pricing\Price;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\CatalogRule\Model\Rule as RuleModel;
use Magento\CatalogRuleStaging\Pricing\Price\CatalogRulePrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Staging\Model\VersionManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Magento\CatalogRuleStaging\Pricing\Price\CatalogRulePrice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogRulePriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CatalogRulePrice
     */
    private $price;

    /**
     * @var Product|MockObject
     */
    private $saleableItemMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $dataTimeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|MockObject
     */
    private $priceInfoMock;

    /**
     * @var RuleResourceModel|MockObject
     */
    private $catalogRuleResourceMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $coreStoreMock;

    /**
     * @var Calculator|MockObject
     */
    private $calculator;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory|MockObject
     */
    private $collectionFactory;

    /**
     * @var VersionManager|MockObject
     */
    private $versionManagerMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $catalogRepositoryMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->saleableItemMock = $this->createPartialMock(
            Product::class,
            ['getPrice', 'getId', '__wakeup', 'getPriceInfo', 'getParentId']
        );
        $this->dataTimeMock = $this->createMock(TimezoneInterface::class);
        $this->coreStoreMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')
            ->willReturn($this->coreStoreMock);
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->priceInfoMock = $this->createMock(PriceInfoInterface::class);
        $this->catalogRuleResourceMock = $this->createMock(RuleResourceModel::class);
        $this->priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue([]));
        $this->saleableItemMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($this->priceInfoMock));
        $this->calculator = $this->createMock(Calculator::class);
        $qty = 1;
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory::class,
            ['create']
        );
        $this->versionManagerMock = $this->createMock(VersionManager::class);
        $this->catalogRepositoryMock = $this->createMock(ProductRepositoryInterface::class);

        $this->price = new CatalogRulePrice(
            $this->saleableItemMock,
            $qty,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->dataTimeMock,
            $this->storeManagerMock,
            $this->customerSessionMock,
            $this->catalogRuleResourceMock,
            $this->collectionFactory,
            $this->versionManagerMock,
            $this->catalogRepositoryMock
        );
    }

    /**
     * @dataProvider getValueDataProvider
     * @param string $simpleAction
     * @param float $expectedPrice
     * @return void
     */
    public function testGetValue(string $simpleAction, float $expectedPrice)
    {
        $price = 10;
        $websiteId = 1;
        $customerGroupId = 2;
        $ruleCollection =
            $this->createMock(RuleCollection::class);
        $ruleMock = $this->createMock(RuleModel::class);
        $this->versionManagerMock->expects($this->once())->method('isPreviewVersion')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getPrice')->willReturn($price);
        $this->coreStoreMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())->method('addWebsiteFilter')->with($websiteId)->willReturnSelf();
        $this->saleableItemMock->expects($this->once())->method('getParentId')->willReturn(false);
        $this->catalogRepositoryMock->expects($this->never())->method('getById');
        $ruleCollection
            ->expects($this->once())
            ->method('addCustomerGroupFilter')
            ->with($customerGroupId)
            ->willReturnSelf();
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->with('is_active', 1)->willReturnSelf();
        $ruleCollection
            ->expects($this->once())
            ->method('setOrder')
            ->with('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->willReturn([$ruleMock]);
        $ruleMock->expects($this->once())->method('validate')->with($this->saleableItemMock)->willReturn(true);
        $ruleMock->expects($this->once())->method('getSimpleAction')->willReturn($simpleAction);
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(5);
        $this->priceCurrencyMock->expects($this->once())->method('round')->willReturn($expectedPrice);
        $ruleMock->expects($this->once())->method('getStopRulesProcessing')->willReturn(true);
        $this->assertEquals($expectedPrice, $this->price->getValue());
    }

    /**
     * @return array
     */
    public function getValueDataProvider(): array
    {
        return [
            ['to_fixed', 5],
            ['to_percent', 0.5],
            ['by_fixed', 5],
            ['by_percent', 9.5]
        ];
    }

    /**
     * @return void
     */
    public function testGetValueForDefaultPrice()
    {
        $price = 10;
        $websiteId = 1;
        $customerGroupId = 2;
        $expectedPrice = 0;
        $ruleCollection =
            $this->createMock(RuleCollection::class);
        $ruleMock = $this->createMock(RuleModel::class);
        $this->versionManagerMock->expects($this->once())->method('isPreviewVersion')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getPrice')->willReturn($price);
        $this->coreStoreMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())->method('addWebsiteFilter')->with($websiteId)->willReturnSelf();
        $ruleCollection
            ->expects($this->once())
            ->method('addCustomerGroupFilter')
            ->with($customerGroupId)
            ->willReturnSelf();
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->with('is_active', 1)->willReturnSelf();
        $ruleCollection
            ->expects($this->once())
            ->method('setOrder')
            ->with('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->willReturn([$ruleMock]);
        $ruleMock->expects($this->once())->method('validate')->with($this->saleableItemMock)->willReturn(true);
        $ruleMock->expects($this->once())->method('getSimpleAction')->willReturn('default');
        $ruleMock->expects($this->never())->method('getDiscountAmount');
        $this->priceCurrencyMock->expects($this->once())->method('round')->willReturn($expectedPrice);
        $ruleMock->expects($this->once())->method('getStopRulesProcessing')->willReturn(false);
        $this->assertEquals($expectedPrice, $this->price->getValue());
    }

    /**
     * @return void
     */
    public function testGetValueForInvalidProduct()
    {
        $price = 10;
        $websiteId = 1;
        $customerGroupId = 2;
        $ruleCollection =
            $this->createMock(RuleCollection::class);
        $ruleMock = $this->createMock(RuleModel::class);
        $this->versionManagerMock->expects($this->once())->method('isPreviewVersion')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getPrice')->willReturn($price);
        $this->coreStoreMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())->method('addWebsiteFilter')->with($websiteId)->willReturnSelf();
        $ruleCollection
            ->expects($this->once())
            ->method('addCustomerGroupFilter')
            ->with($customerGroupId)
            ->willReturnSelf();
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->with('is_active', 1)->willReturnSelf();
        $ruleCollection
            ->expects($this->once())
            ->method('setOrder')
            ->with('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->willReturn([$ruleMock]);
        $ruleMock->expects($this->once())->method('validate')->with($this->saleableItemMock)->willReturn(false);
        $ruleMock->expects($this->never())->method('getSimpleAction')->willReturn('default');
        $ruleMock->expects($this->never())->method('getDiscountAmount');
        $this->priceCurrencyMock->expects($this->never())->method('round');
        $ruleMock->expects($this->never())->method('getStopRulesProcessing');
        $this->assertEquals($price, $this->price->getValue());
    }

    /**
     * @return void
     */
    public function testGetValueForConfigurableProduct()
    {
        $price = 10;
        $websiteId = 1;
        $customerGroupId = 2;
        $ruleCollection =
            $this->createMock(RuleCollection::class);
        $saleableItem = $this->createPartialMock(
            Product::class,
            ['getPrice', 'getId', '__wakeup', 'getPriceInfo', 'getParentId']
        );
        $ruleMock = $this->createMock(RuleModel::class);
        $this->versionManagerMock->expects($this->once())->method('isPreviewVersion')->willReturn(true);
        $this->saleableItemMock->expects($this->once())->method('getPrice')->willReturn($price);
        $this->coreStoreMock->expects($this->once())->method('getWebsiteId')->willReturn($websiteId);
        $this->customerSessionMock->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($ruleCollection);
        $ruleCollection->expects($this->once())->method('addWebsiteFilter')->with($websiteId)->willReturnSelf();
        $this->saleableItemMock->expects($this->exactly(2))->method('getParentId')->willReturn(2);
        $this->catalogRepositoryMock->expects($this->once())->method('getById')->with(2)->willReturn($saleableItem);
        $ruleCollection
            ->expects($this->once())
            ->method('addCustomerGroupFilter')
            ->with($customerGroupId)
            ->willReturnSelf();
        $ruleCollection->expects($this->once())->method('addFieldToFilter')->with('is_active', 1)->willReturnSelf();
        $ruleCollection
            ->expects($this->once())
            ->method('setOrder')
            ->with('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->willReturn([$ruleMock]);
        $ruleMock->expects($this->once())->method('validate')->with($saleableItem)->willReturn(true);
        $ruleMock->expects($this->once())->method('getSimpleAction')->willReturn('to_fixed');
        $ruleMock->expects($this->once())->method('getDiscountAmount')->willReturn(5);
        $this->priceCurrencyMock->expects($this->once())->method('round')->willReturn(5);
        $ruleMock->expects($this->once())->method('getStopRulesProcessing')->willReturn(true);
        $this->assertEquals(5, $this->price->getValue());
    }
}
