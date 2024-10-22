<?php
namespace Magento\CustomerCustomAttributes\Test\Unit\Observer;

class EnterpriseCustomerAddressAttributeSaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CustomerCustomAttributes\Observer\EnterpriseCustomerAddressAttributeSave
     */
    protected $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteAddressFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAddressFactory;

    protected function setUp()
    {
        $this->quoteAddressFactory = $this->getMockBuilder(
            \Magento\CustomerCustomAttributes\Model\Sales\Quote\AddressFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->orderAddressFactory = $this->getMockBuilder(
            \Magento\CustomerCustomAttributes\Model\Sales\Order\AddressFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $this->observer = new \Magento\CustomerCustomAttributes\Observer\EnterpriseCustomerAddressAttributeSave(
            $this->orderAddressFactory,
            $this->quoteAddressFactory
        );
    }

    public function testEnterpriseCustomerAddressAttributeSave()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock();

        $dataModel = $this->getMockBuilder(\Magento\Customer\Model\Attribute::class)
            ->setMethods(['__wakeup', 'isObjectNew'])
            ->disableOriginalConstructor()
            ->getMock();

        $orderAddress = $this->getMockBuilder(\Magento\CustomerCustomAttributes\Model\Sales\Order\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteAddress = $this->getMockBuilder(\Magento\CustomerCustomAttributes\Model\Sales\Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dataModel->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $observer->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $event->expects($this->once())->method('getAttribute')->will($this->returnValue($dataModel));
        $quoteAddress->expects($this->once())->method('saveNewAttribute')->with($dataModel)->will($this->returnSelf());
        $this->quoteAddressFactory->expects($this->once())->method('create')->will($this->returnValue($quoteAddress));
        $orderAddress->expects($this->once())->method('saveNewAttribute')->with($dataModel)->will($this->returnSelf());
        $this->orderAddressFactory->expects($this->once())->method('create')->will($this->returnValue($orderAddress));
        /** @var \Magento\Framework\Event\Observer $observer */

        $this->assertInstanceOf(
            \Magento\CustomerCustomAttributes\Observer\EnterpriseCustomerAddressAttributeSave::class,
            $this->observer->execute($observer)
        );
    }
}
