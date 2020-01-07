<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Config\Backend;

/**
 * Class ShippingTest
 */
class ShippingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Config\Backend\Shipping
     */
    private $model;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxRecalculate;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxHelper;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->taxRecalculate = $this->createMock(\Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate::class);
        $this->taxHelper = $this->createMock(\Magento\Tax\Helper\Data::class);

        $registry = $this->createMock(\Magento\Framework\Registry::class);
        $config = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            [],
            '',
            false
        );
        $cacheTypeList = $this->getMockForAbstractClass(
            \Magento\Framework\App\Cache\TypeListInterface::class,
            [],
            '',
            false
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Config\Backend\Shipping::class,
            [
                'registry' => $registry,
                'config' => $config,
                'cacheTypeList' => $cacheTypeList,
                'taxRecalculate' => $this->taxRecalculate,
                'taxHelper' => $this->taxHelper,
            ]
        );
    }

    /**
     * Test for method afterSave with origin
     */
    public function testAfterSaveWithOrigin()
    {
        $this->taxHelper->expects($this->any())->method('getTaxBasedOn')->willReturn('origin');
        $this->taxRecalculate->expects($this->any())->method('setNeedRecalculate')->willReturnSelf();

        $this->assertInstanceOf(get_class($this->model), $this->model->afterSave());
    }

    /**
     * Test for method afterSave without origin
     */
    public function testAfterSaveWithoutOrigin()
    {
        $this->taxHelper->expects($this->any())->method('getTaxBasedOn')->willReturn(null);

        $this->assertInstanceOf(get_class($this->model), $this->model->afterSave());
    }
}
