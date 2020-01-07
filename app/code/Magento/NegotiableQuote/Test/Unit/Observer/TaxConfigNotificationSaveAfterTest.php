<?php

namespace Magento\NegotiableQuote\Test\Unit\Observer;

/**
 * Test for Magento\NegotiableQuote\Observer\TaxConfigNotificationSaveAfter class.
 */
class TaxConfigNotificationSaveAfterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $taxRecalculate;

    /**
     * @var \Magento\NegotiableQuote\Observer\TaxConfigNotificationSaveAfter
     */
    private $observer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->taxRecalculate = $this->getMockBuilder(
            \Magento\NegotiableQuote\Model\NegotiableQuoteTaxRecalculate::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->observer = $objectManager->getObject(
            \Magento\NegotiableQuote\Observer\TaxConfigNotificationSaveAfter::class,
            [
                'taxRecalculate' => $this->taxRecalculate,
            ]
        );
    }

    public function testExecute()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDataObject'])
            ->getMock();
        $dataObject = $this->getMockBuilder(\Magento\Tax\Model\Config\Notification::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValueChanged', 'getPath'])
            ->getMock();
        $observer->expects($this->exactly(2))->method('getDataObject')->willReturn($dataObject);
        $dataObject->expects($this->once())->method('isValueChanged')->willReturn(true);
        $dataObject->expects($this->once())->method('getPath')
            ->willReturn(\Magento\Tax\Model\Config::CONFIG_XML_PATH_BASED_ON);
        $this->taxRecalculate->expects($this->once())->method('recalculateTax')->with(true);

        $this->observer->execute($observer);
    }

    public function testNotExecuteWhenChangedNotBasedOnConfig()
    {
        $observer = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDataObject'])
            ->getMock();
        $dataObject = $this->getMockBuilder(\Magento\Tax\Model\Config\Notification::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValueChanged', 'getPath'])
            ->getMock();
        $observer->expects($this->once())->method('getDataObject')->willReturn($dataObject);
        $dataObject->expects($this->never())->method('isValueChanged');
        $dataObject->expects($this->once())->method('getPath')->willReturn('another config path');
        $this->taxRecalculate->expects($this->never())->method('recalculateTax');

        $this->observer->execute($observer);
    }
}
