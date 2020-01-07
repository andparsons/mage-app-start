<?php
namespace Magento\RequisitionList\Test\Unit\Model\RequisitionListItem\Options;

/**
 * Unit test for builder.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepository;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactory;

    /**
     * @var \Magento\RequisitionList\Model\OptionsManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionsManagement;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder
     */
    private $builder;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productHelper;

    /**
     * @var \Magento\RequisitionList\Model\RequisitionListItem\Locator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requisitionListItemLocator;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productRepository = $this->getMockBuilder(\Magento\Catalog\Api\ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getById', 'getParentProductId'])
            ->getMockForAbstractClass();
        $this->optionFactory = $this
            ->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\OptionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->optionsManagement = $this->getMockBuilder(\Magento\RequisitionList\Model\OptionsManagement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->productHelper = $this->getMockBuilder(\Magento\Catalog\Helper\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['addParamsToBuyRequest'])
            ->getMock();
        $this->requisitionListItemLocator =
            $this->getMockBuilder(\Magento\RequisitionList\Model\RequisitionListItem\Locator::class)
                ->disableOriginalConstructor()
                ->setMethods(['getItem'])
                ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->builder = $objectManager->getObject(
            \Magento\RequisitionList\Model\RequisitionListItem\Options\Builder::class,
            [
                'storeManager' => $this->storeManager,
                'productRepository' => $this->productRepository,
                'optionFactory' => $this->optionFactory,
                'optionsManagement' => $this->optionsManagement,
                'serializer' => $this->serializer,
                'productHelper' => $this->productHelper,
                'requisitionListItemLocator' => $this->requisitionListItemLocator
            ]
        );
    }

    /**
     * Test for build().
     *
     * @return void
     */
    public function testBuild()
    {
        $itemId = 1;
        $buyRequest = ['product' => 123];
        $itemProductOption = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $itemProductOption->expects($this->atLeastOnce())->method('getValue')->willReturn('option_value');
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        $item = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemLocator->expects($this->once())->method('getItem')->willReturn($item);
        $this->productHelper->expects($this->once())->method('addParamsToBuyRequest')
            ->willReturn(new \Magento\Framework\DataObject($buyRequest));
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentProductId', 'getTypeInstance', 'getCustomOptions'])
            ->getMock();
        $product->expects($this->atLeastOnce())->method('getTypeInstance')->willReturn($typeInstance);
        $product->expects($this->atLeastOnce())->method('getParentProductId')->willReturn(null);
        $product->expects($this->atLeastOnce())->method('getCustomOptions')->willReturn([$itemProductOption]);
        $typeInstance->expects($this->atLeastOnce())->method('processConfiguration')->willReturn([$product]);
        $this->productRepository->expects($this->atLeastOnce())->method('getById')->willReturn($product);
        $this->optionsManagement->expects($this->atLeastOnce())->method('addOption');
        $this->optionsManagement->expects($this->atLeastOnce())->method('getOptionsByRequisitionListItemId')
            ->willReturn(['option' => $itemProductOption]);

        $this->assertEquals(['option' => 'option_value'], $this->builder->build($buyRequest, $itemId, false));
    }

    /**
     * Test for build() with empty product id.
     *
     * @return void
     */
    public function testBuildWithEmptyProductId()
    {
        $itemId = 1;
        $buyRequest = [];

        $this->assertEquals(['info_buyRequest' => []], $this->builder->build($buyRequest, $itemId, false));
    }

    /**
     * Test for build() with param unserialization.
     *
     * @param array|string $infoBuyRequest
     * @param array $infoBuyRequestData
     * @param array $result
     * @param int $unserializeInvokesCount
     * @return void
     *
     * @dataProvider buildWithUnserializeDataProvider
     */
    public function testBuildWithUnserialize(
        $infoBuyRequest,
        array $infoBuyRequestData,
        array $result,
        $unserializeInvokesCount
    ) {
        $itemId = 1;
        $buyRequest = ['product' => 123];
        $itemProductOption = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $itemProductOption->expects($this->atLeastOnce())->method('getValue')->willReturn($infoBuyRequest);
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        $item = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemLocator->expects($this->once())->method('getItem')->willReturn($item);
        $this->productHelper->expects($this->once())->method('addParamsToBuyRequest')
            ->willReturn(new \Magento\Framework\DataObject($buyRequest));
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParentProductId', 'getTypeInstance', 'getCustomOptions'])
            ->getMock();
        $product->expects($this->atLeastOnce())->method('getTypeInstance')->willReturn($typeInstance);
        $product->expects($this->atLeastOnce())->method('getParentProductId')->willReturn(null);
        $product->expects($this->atLeastOnce())->method('getCustomOptions')->willReturn([$itemProductOption]);
        $typeInstance->expects($this->atLeastOnce())->method('processConfiguration')->willReturn([$product]);
        $this->productRepository->expects($this->atLeastOnce())->method('getById')->willReturn($product);
        $this->optionsManagement->expects($this->atLeastOnce())->method('addOption');
        $this->optionsManagement->expects($this->atLeastOnce())->method('getOptionsByRequisitionListItemId')
            ->willReturn(['info_buyRequest' => $itemProductOption]);
        $this->serializer->expects($this->exactly($unserializeInvokesCount))->method('unserialize')
            ->willReturn($infoBuyRequestData);

        $this->assertEquals($result, $this->builder->build($buyRequest, $itemId, false));
    }

    /**
     * Test for build() with LocalizedException.
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testBuildWithLocalizedException()
    {
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        $phrase = new \Magento\Framework\Phrase('Exception');
        $exception = new \Magento\Framework\Exception\NoSuchEntityException(__($phrase));
        $this->productRepository->expects($this->atLeastOnce())->method('getById')->willThrowException($exception);

        $this->builder->build(['product' => 123], 1, false);
    }

    /**
     * Test for build() for misconfigured complex product.
     *
     * @return void
     */
    public function testBuildForMisconfiguredProduct()
    {
        $itemId = 1;
        $buyRequest = ['product' => 123];
        $store = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $store->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->atLeastOnce())->method('getStore')->willReturn($store);
        $item = $this
            ->getMockBuilder(\Magento\RequisitionList\Api\Data\RequisitionListItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->requisitionListItemLocator->expects($this->once())->method('getItem')->willReturn($item);
        $this->productHelper->expects($this->once())->method('addParamsToBuyRequest')
            ->willReturn(new \Magento\Framework\DataObject($buyRequest));
        $typeInstance = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeInstance'])
            ->getMock();
        $product->expects($this->atLeastOnce())->method('getTypeInstance')->willReturn($typeInstance);
        $typeInstance->expects($this->atLeastOnce())->method('processConfiguration')->willReturn('error message');
        $this->productRepository->expects($this->atLeastOnce())->method('getById')->willReturn($product);

        $this->assertEquals([], $this->builder->build($buyRequest, $itemId, true));
    }

    /**
     * Data provider for test testBuildWithUnserialize.
     *
     * @return array
     */
    public function buildWithUnserializeDataProvider()
    {
        return [
            [
                json_encode(['key' => 'value']),
                ['key' => 'value'],
                ['info_buyRequest' => ['key' => 'value']],
                1
            ],
            [
                ['key' => 'value'],
                ['key' => 'value'],
                ['info_buyRequest' => ['key' => 'value']],
                0
            ]
        ];
    }
}
