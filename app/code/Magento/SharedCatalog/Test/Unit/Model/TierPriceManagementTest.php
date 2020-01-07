<?php

namespace Magento\SharedCatalog\Test\Unit\Model;

use Magento\Catalog\Api\Data\PriceUpdateResultInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\TierPriceStorageInterface;
use Magento\Catalog\Model\Product\Price\TierPriceFactory;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\SharedCatalog\Api\Data\SharedCatalogInterface;
use Magento\SharedCatalog\Model\TierPriceManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for TierPriceManagement model.
 */
class TierPriceManagementTest extends TestCase
{
    /**
     * @var TierPriceStorageInterface|MockObject
     */
    private $tierPriceStorage;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $customerGroupRepository;

    /**
     * @var TierPriceFactory|MockObject
     */
    private $tierPriceFactory;

    /**
     * @var TierPriceManagement
     */
    private $tierPriceManagement;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->tierPriceFactory = $this->createMock(TierPriceFactory::class);
        $this->customerGroupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->tierPriceStorage = $this->createMock(TierPriceStorageInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPriceManagement = $objectManager->getObject(
            TierPriceManagement::class,
            [
                'tierPriceStorage' => $this->tierPriceStorage,
                'customerGroupRepository' => $this->customerGroupRepository,
                'tierPriceFactory' => $this->tierPriceFactory,
                'batchSize' => 1,
            ]
        );
    }

    /**
     * Test for deleteProductTierPrices method.
     *
     * @return void
     */
    public function testDeleteProductTierPrices()
    {
        $customerGroupId = 1;
        $customerGroupCode = 'general';
        $productSkus = ['SKU1', 'SKU2'];
        $sharedCatalog = $this->getMockBuilder(SharedCatalogInterface::class)
            ->disableOriginalConstructor()->getMock();
        $sharedCatalog->expects($this->once())->method('getCustomerGroupId')->willReturn($customerGroupId);
        $sharedCatalog->expects($this->once())->method('getType')
            ->willReturn(SharedCatalogInterface::TYPE_PUBLIC);
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository->expects($this->once())
            ->method('getById')->with($customerGroupId)->willReturn($customerGroup);
        $customerGroup->expects($this->once())->method('getCode')->willReturn($customerGroupCode);
        $tierPrice = $this->getMockBuilder(TierPriceInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->exactly(2))->method('get')
            ->withConsecutive([[$productSkus[0]]], [[$productSkus[1]]])->willReturn([$tierPrice]);
        $tierPrice->expects($this->exactly(2))->method('getQuantity')->willReturn(1);
        $tierPrice->expects($this->exactly(2))->method('getCustomerGroup')->willReturn($customerGroupCode);
        $priceUpdateResult = $this->getMockBuilder(PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->exactly(2))
            ->method('delete')->with([$tierPrice])->willReturn($priceUpdateResult);
        $this->tierPriceManagement->deleteProductTierPrices($sharedCatalog, $productSkus, true);
    }

    /**
     * Test for updateProductTierPrices method.
     *
     * @return void
     */
    public function testUpdateProductTierPrices()
    {
        $customerGroupId = 1;
        $sku = 'product_sku';
        $tierPricesData = [
            [
                'qty' => 1,
                'website_id' => 2,
                'percentage_value' => 30,
            ],
            [
                'qty' => 3,
                'website_id' => 4,
                'value' => 15,
            ],
        ];

        $sharedCatalog = $this->createMock(SharedCatalogInterface::class);
        $sharedCatalog->expects($this->exactly(2))
            ->method('getCustomerGroupId')
            ->willReturn($customerGroupId);
        $sharedCatalog->expects($this->atLeastOnce())
            ->method('getType')
            ->willReturn(SharedCatalogInterface::TYPE_PUBLIC);
        $tierPrice = $this->createMock(TierPriceInterface::class);
        $createTierPriceArgs = [
            [
                $tierPricesData[0] + ['all_groups' => 0, 'customer_group_id' => $customerGroupId],
                $sku
            ],
            [
                $tierPricesData[0] + ['all_groups' => 0, 'customer_group_id' => GroupInterface::NOT_LOGGED_IN_ID],
                $sku
            ],
            [
                $tierPricesData[1] + ['all_groups' => 0, 'customer_group_id' => $customerGroupId],
                $sku
            ],
            [
                $tierPricesData[1] + ['all_groups' => 0, 'customer_group_id' => GroupInterface::NOT_LOGGED_IN_ID],
                $sku
            ],
        ];
        $this->tierPriceFactory->expects($this->exactly(4))
            ->method('create')
            ->withConsecutive(...$createTierPriceArgs)
            ->willReturn($tierPrice);
        $priceUpdateResult = $this->createMock(PriceUpdateResultInterface::class);
        $this->tierPriceStorage->expects($this->once())
            ->method('update')
            ->with([$tierPrice, $tierPrice, $tierPrice, $tierPrice])
            ->willReturn($priceUpdateResult);
        $this->tierPriceManagement->updateProductTierPrices($sharedCatalog, $sku, $tierPricesData);
    }

    /**
     * Test for deletePublicTierPrices method.
     *
     * @return void
     */
    public function testDeletePublicTierPrices()
    {
        $customerGroupCode = 'not_logged_in';
        $productSkus = ['SKU1', 'SKU2'];
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository->expects($this->once())->method('getById')->with(0)->willReturn($customerGroup);
        $customerGroup->expects($this->once())->method('getCode')->willReturn($customerGroupCode);
        $tierPrice = $this->getMockBuilder(TierPriceInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->once())->method('get')->with($productSkus)->willReturn([$tierPrice]);
        $tierPrice->expects($this->once())->method('getCustomerGroup')->willReturn($customerGroupCode);
        $priceUpdateResult = $this->getMockBuilder(PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->once())
            ->method('delete')->with([$tierPrice])->willReturn($priceUpdateResult);
        $this->tierPriceManagement->deletePublicTierPrices($productSkus);
    }

    /**
     * Test for getItemPrices method.
     *
     * @return void
     */
    public function testGetItemPrices()
    {
        $customerGroupId = 1;
        $customerGroupCode = 'general';
        $productSkus = ['SKU1', 'SKU2'];
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository->expects($this->once())
            ->method('getById')->with($customerGroupId)->willReturn($customerGroup);
        $customerGroup->expects($this->once())->method('getCode')->willReturn($customerGroupCode);
        $tierPrice = $this->getMockBuilder(TierPriceInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->once())
            ->method('get')->with($productSkus)->willReturn([$tierPrice, $tierPrice]);
        $tierPrice->expects($this->exactly(2))->method('getCustomerGroup')
            ->willReturnOnConsecutiveCalls($customerGroupCode, 'custom_group');
        $tierPrice->expects($this->exactly(2))->method('getQuantity')->willReturn(1);
        $this->assertEquals([$tierPrice], $this->tierPriceManagement->getItemPrices($customerGroupId, $productSkus));
    }

    /**
     * Test for addPricesForPublicCatalog method.
     *
     * @return void
     */
    public function testAddPricesForPublicCatalog()
    {
        $customerGroupId = 1;
        $customerGroupCode = 'general';
        $notLoggedInGroupCode = 'not_logged_in';
        $productSkus = ['SKU1', 'SKU2'];
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository->expects($this->exactly(2))->method('getById')
            ->withConsecutive([$customerGroupId], [0])->willReturn($customerGroup);
        $customerGroup->expects($this->exactly(2))->method('getCode')
            ->willReturnOnConsecutiveCalls($customerGroupCode, $notLoggedInGroupCode);
        $tierPrice = $this->getMockBuilder(TierPriceInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->exactly(2))->method('get')
            ->withConsecutive([[$productSkus[0]]], [[$productSkus[1]]])
            ->willReturnOnConsecutiveCalls([$tierPrice, $tierPrice], []);
        $tierPrice->expects($this->exactly(2))->method('getCustomerGroup')
            ->willReturnOnConsecutiveCalls($customerGroupCode, 'custom_group');
        $tierPrice->expects($this->exactly(2))->method('getQuantity')->willReturn(1);
        $tierPrice->expects($this->once())->method('setCustomerGroup')->with($notLoggedInGroupCode)->willReturnSelf();
        $priceUpdateResult = $this->getMockBuilder(PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPriceStorage->expects($this->once())
            ->method('update')->with([$tierPrice])->willReturn($priceUpdateResult);
        $this->tierPriceManagement->addPricesForPublicCatalog($customerGroupId, $productSkus);
    }
}
