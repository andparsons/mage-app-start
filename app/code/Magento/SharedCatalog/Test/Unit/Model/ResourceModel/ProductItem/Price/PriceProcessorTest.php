<?php
namespace Magento\SharedCatalog\Test\Unit\Model\ResourceModel\ProductItem\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for PriceProcessor.
 */
class PriceProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\Data\TierPriceInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceFactory;

    /**
     * @var \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor
     */
    private $priceProcessor;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->tierPriceFactory = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->priceProcessor = $objectManagerHelper->getObject(
            \Magento\SharedCatalog\Model\ResourceModel\ProductItem\Price\PriceProcessor::class,
            [
                'tierPriceFactory' => $this->tierPriceFactory
            ]
        );
    }

    /**
     * Test for createPricesUpdate().
     *
     * @param string $priceType
     * @return void
     * @dataProvider priceTypeDataProvider
     */
    public function testCreatePricesUpdate($priceType)
    {
        $operationData = $this->getPricesData($priceType);
        $this->preparePriceUpdateMock($priceType);
        $pricesUpdates = $this->priceProcessor->createPricesUpdate($operationData);

        $this->assertInstanceOf(
            \Magento\Catalog\Api\Data\TierPriceInterface::class,
            $pricesUpdates[0]
        );
    }

    /**
     * Test for createPricesDelete().
     *
     * @param string $priceType
     * @return void
     * @dataProvider priceTypeDataProvider
     */
    public function testCreatePricesDelete($priceType)
    {
        $operationData = $this->getPricesData($priceType);
        $this->preparePriceUpdateMock($priceType);
        $pricesDeleteData = $this->priceProcessor->createPricesDelete($operationData);

        $this->assertInstanceOf(
            \Magento\Catalog\Api\Data\TierPriceInterface::class,
            $pricesDeleteData[0]
        );
    }

    /**
     * Test for prepareErrorMessage().
     *
     * @return void
     */
    public function testPrepareErrorMessage()
    {
        $message = '% placeholder message';
        $value = 'error';
        $placeholder = ' placeholder';
        $resultMessage = 'error message';
        $result = $this->getMockBuilder(\Magento\Catalog\Api\Data\PriceUpdateResultInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $result->expects($this->atLeastOnce())->method('getMessage')->willReturn($message);
        $result->expects($this->atLeastOnce())->method('getParameters')->willReturn([$placeholder => $value]);

        $this->assertEquals($resultMessage, $this->priceProcessor->prepareErrorMessage($result));
    }

    /**
     * Prepare price update mock.
     *
     * @param string $priceType
     * @return void
     */
    private function preparePriceUpdateMock($priceType)
    {
        $priceDto = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $priceDto->expects($this->atLeastOnce())->method('setWebsiteId')->willReturnSelf();
        $priceDto->expects($this->atLeastOnce())->method('setSku')->willReturnSelf();
        $priceDto->expects($this->atLeastOnce())->method('setCustomerGroup')->willReturnSelf();
        $priceDto->expects($this->atLeastOnce())->method('setQuantity')->willReturnSelf();
        $priceDto->expects($this->atLeastOnce())->method('setPrice')->willReturnSelf();
        $priceDto->expects($this->atLeastOnce())->method('setPriceType')->with($priceType)->willReturnSelf();
        $this->tierPriceFactory->expects($this->atLeastOnce())->method('create')->willReturn($priceDto);
    }

    /**
     * Prepare prices data.
     *
     * @param string $priceType
     * @return array
     */
    private function getPricesData($priceType)
    {
        return [
            'product_sku' => 'sku',
            'customer_group' => 4,
            'prices' => [
                [
                    'is_deleted' => false,
                    'website_id' => 1,
                    'qty' => 2,
                    'value_type' => $priceType,
                    'price' => 20,
                    'percentage_value' => 20
                ],
                [
                    'is_deleted' => true,
                    'website_id' => 1,
                    'qty' => 2,
                    'value_type' => $priceType,
                    'price' => 20,
                    'percentage_value' => 20
                ]
            ]
        ];
    }

    /**
     * Price type DataProvider.
     *
     * @return array
     */
    public function priceTypeDataProvider()
    {
        return [
            [\Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED],
            [\Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT]
        ];
    }
}
