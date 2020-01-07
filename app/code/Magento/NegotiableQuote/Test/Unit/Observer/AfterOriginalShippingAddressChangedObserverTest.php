<?php
namespace Magento\NegotiableQuote\Test\Unit\Observer;

use Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate;
use Magento\Tax\Helper\Data;

/**
 * Class AfterOriginalShippingAddressChangedObserverTest
 */
class AfterOriginalShippingAddressChangedObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\NegotiableQuote\Observer\AfterOriginalShippingAddressChangedObserver
     */
    private $afterOriginalShippingAddressChangedObserver;

    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxRecalculateMock;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxConfigMock;

    /**
     * Set up.
     *
     * @return void
     */
    public function setUp()
    {
        $this->taxRecalculateMock = $this->getMockBuilder(NegotiableQuoteTaxRecalculate::class)
            ->disableOriginalConstructor()
            ->setMethods(['recalculateTax'])
            ->getMock();

        $this->taxConfigMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTaxBasedOn'])
            ->getMock();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->afterOriginalShippingAddressChangedObserver = $this->objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Observer\AfterOriginalShippingAddressChangedObserver::class,
            [
                'taxRecalculate' => $this->taxRecalculateMock,
                'taxConfig' => $this->taxConfigMock,
            ]
        );
    }

    /**
     * A test for execute() method.
     *
     * @return void
     */
    public function testExecute()
    {
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $observer */
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->setMethods(['getDataObject'])
            ->disableOriginalConstructor()->getMock();

        $this->taxConfigMock->expects($this->once())->method('getTaxBasedOn')->willReturn('origin');
        $this->taxRecalculateMock->expects($this->once())->method('recalculateTax');

        $this->afterOriginalShippingAddressChangedObserver->execute($observer);
    }
}
