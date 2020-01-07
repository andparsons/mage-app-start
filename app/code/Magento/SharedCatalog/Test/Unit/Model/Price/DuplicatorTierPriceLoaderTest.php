<?php
namespace Magento\SharedCatalog\Test\Unit\Model\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;

/**
 * Unit test for TierPricesLoader.
 */
class DuplicatorTierPriceLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\TierPriceStorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceStorage;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRepository;

    /**
     * @var \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader
     */
    private $tierPriceLoader;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->tierPriceStorage = $this->getMockBuilder(\Magento\Catalog\Api\TierPriceStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerGroupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->tierPriceLoader = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\Price\DuplicatorTierPriceLoader::class,
            [
                'tierPriceStorage' => $this->tierPriceStorage,
                'customerGroupRepository' => $this->customerGroupRepository,
            ]
        );
    }

    /**
     * Unit test for load().
     *
     * @param string $priceType
     * @param string $priceTypeValue
     * @param string $valueKey
     * @return void
     * @dataProvider loadDataProvider
     */
    public function testLoad($priceType, $priceTypeValue, $valueKey)
    {
        $sku = 'sku';
        $skus = [$sku];
        $customerGroupId = 1;
        $qty = 2;
        $websiteId = 1;
        $price = 10.00;
        $customerGroupCode = 'Customer Group';
        $tierPrice = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $tierPrice->expects($this->atLeastOnce())->method('getCustomerGroup')->willReturn($customerGroupCode);
        $tierPrice->expects($this->atLeastOnce())->method('getQuantity')->willReturn($qty);
        $tierPrice->expects($this->atLeastOnce())->method('getWebsiteId')->willReturn($websiteId);
        $tierPrice->expects($this->atLeastOnce())->method('getPriceType')->willReturn($priceType);
        $tierPrice->expects($this->atLeastOnce())->method('getPrice')->willReturn($price);
        $tierPrice->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $this->tierPriceStorage->expects($this->atLeastOnce())->method('get')->with($skus)->willReturn([$tierPrice]);
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getCode')->willReturn($customerGroupCode);
        $this->customerGroupRepository->expects($this->atLeastOnce())->method('getById')->with($customerGroupId)
            ->willReturn($customerGroup);
        $result = [
            $sku => [
                [
                    'qty' => $qty,
                    'website_id' => $websiteId,
                    'is_changed' => true,
                    $valueKey => $price,
                    'value_type' => $priceTypeValue
                ]
            ]
        ];

        $this->assertEquals($result, $this->tierPriceLoader->load($skus, $customerGroupId));
    }

    /**
     * DataProvider for testLoad().
     *
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            [TierPriceInterface::PRICE_TYPE_FIXED, ProductPriceOptionsInterface::VALUE_FIXED, 'price'],
            [TierPriceInterface::PRICE_TYPE_DISCOUNT, ProductPriceOptionsInterface::VALUE_PERCENT, 'percentage_value']
        ];
    }
}
