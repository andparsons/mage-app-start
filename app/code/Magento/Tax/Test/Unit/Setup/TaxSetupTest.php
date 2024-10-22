<?php
namespace Magento\Tax\Test\Unit\Setup;

class TaxSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Setup\TaxSetup
     */
    protected $taxSetup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeConfigMock;

    protected function setUp()
    {
        $this->typeConfigMock = $this->createMock(\Magento\Catalog\Model\ProductTypes\ConfigInterface::class);

        $salesSetup = $this->createMock(\Magento\Sales\Setup\SalesSetup::class);
        $salesSetupFactory = $this->createPartialMock(\Magento\Sales\Setup\SalesSetupFactory::class, ['create']);
        $salesSetupFactory->expects($this->any())->method('create')->will($this->returnValue($salesSetup));

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxSetup = $helper->getObject(
            \Magento\Tax\Setup\TaxSetup::class,
            [
                'productTypeConfig' => $this->typeConfigMock,
                'salesSetupFactory' => $salesSetupFactory,
            ]
        );
    }

    public function testGetTaxableItems()
    {
        $refundable = ['simple', 'simple2'];
        $this->typeConfigMock->expects(
            $this->once()
        )->method(
            'filter'
        )->with(
            'taxable'
        )->will(
            $this->returnValue($refundable)
        );
        $this->assertEquals($refundable, $this->taxSetup->getTaxableItems());
    }
}
