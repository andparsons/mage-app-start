<?php

namespace Magento\Shipping\Test\Unit\Model\Carrier;

use \Magento\Shipping\Model\Carrier\AbstractCarrierOnline;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class AbstractCarrierOnlineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test identification number of product
     *
     * @var int
     */
    protected $productId = 1;

    /**
     * @var AbstractCarrierOnline|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $carrier;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockItemData;

    protected function setUp()
    {
        $this->stockRegistry = $this->createMock(\Magento\CatalogInventory\Model\StockRegistry::class);
        $this->stockItemData = $this->createMock(\Magento\CatalogInventory\Model\Stock\Item::class);

        $this->stockRegistry->expects($this->any())->method('getStockItem')
            ->with($this->productId, 10)
            ->will($this->returnValue($this->stockItemData));

        $objectManagerHelper = new ObjectManagerHelper($this);
        $carrierArgs = $objectManagerHelper->getConstructArguments(
            \Magento\Shipping\Model\Carrier\AbstractCarrierOnline::class,
            [
                'stockRegistry' => $this->stockRegistry,
                'xmlSecurity' => new \Magento\Framework\Xml\Security(),
            ]
        );
        $this->carrier = $this->getMockBuilder(\Magento\Shipping\Model\Carrier\AbstractCarrierOnline::class)
            ->setConstructorArgs($carrierArgs)
            ->setMethods(['getConfigData', '_doShipmentRequest', 'collectRates'])
            ->getMock();
    }

    /**
     * @covers \Magento\Shipping\Model\Shipping::composePackagesForCarrier
     */
    public function testComposePackages()
    {
        $this->carrier->expects($this->any())->method('getConfigData')->will($this->returnCallback(function ($key) {
            $configData = [
                'max_package_weight' => 10,
                'showmethod'         => 1,
            ];
            return isset($configData[$key]) ? $configData[$key] : 0;
        }));

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getId')->will($this->returnValue($this->productId));

        $item = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct', 'getQty', 'getWeight', '__wakeup', 'getStore'])
            ->getMock();
        $item->expects($this->any())->method('getProduct')->will($this->returnValue($product));

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getWebsiteId']);
        $store->expects($this->any())
            ->method('getWebsiteId')
            ->will($this->returnValue(10));
        $item->expects($this->any())->method('getStore')->will($this->returnValue($store));

        $request = new RateRequest();
        $request->setData('all_items', [$item]);
        $request->setData('dest_postcode', 1);

        /** Testable service calls to CatalogInventory module */
        $this->stockRegistry->expects($this->atLeastOnce())->method('getStockItem')->with($this->productId);
        $this->stockItemData->expects($this->atLeastOnce())->method('getEnableQtyIncrements')
            ->will($this->returnValue(true));
        $this->stockItemData->expects($this->atLeastOnce())->method('getQtyIncrements')
            ->will($this->returnValue(5));
        $this->stockItemData->expects($this->atLeastOnce())->method('getIsQtyDecimal')->will($this->returnValue(true));
        $this->stockItemData->expects($this->atLeastOnce())->method('getIsDecimalDivided')
            ->will($this->returnValue(true));

        $this->carrier->processAdditionalValidation($request);
    }

    public function testParseXml()
    {
        $xmlString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><GetResponse><value>42</value>></GetResponse>";
        $simpleXmlElement = $this->carrier->parseXml($xmlString);
        $this->assertEquals('GetResponse', $simpleXmlElement->getName());
        $this->assertEquals(42, (int)$simpleXmlElement->value);
        $this->assertInstanceOf('SimpleXMLElement', $simpleXmlElement);
        $customSimpleXmlElement = $this->carrier->parseXml(
            $xmlString,
            \Magento\Shipping\Model\Simplexml\Element::class
        );
        $this->assertInstanceOf(\Magento\Shipping\Model\Simplexml\Element::class, $customSimpleXmlElement);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The security validation of the XML document has failed.
     */
    public function testParseXmlXXEXml()
    {
        $xmlString = '<!DOCTYPE scan [
            <!ENTITY test SYSTEM "php://filter/read=convert.base64-encode/resource='
            . __DIR__ . '/AbstractCarrierOnline/xxe-xml.txt">]><scan>&test;</scan>';

        $xmlElement = $this->carrier->parseXml($xmlString);

        // @codingStandardsIgnoreLine
        echo $xmlElement->asXML();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage The security validation of the XML document has failed.
     */
    public function testParseXmlXQBXml()
    {
        $xmlString = '<?xml version="1.0"?>
            <!DOCTYPE test [
              <!ENTITY value "value">
              <!ENTITY value1 "&value;&value;&value;&value;&value;&value;&value;&value;&value;&value;">
              <!ENTITY value2 "&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;&value1;">
            ]>
            <test>&value2;</test>';

        $this->carrier->parseXml($xmlString);
    }
}
