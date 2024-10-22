<?php
namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SalesQuoteSaveAfterObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Observer\SalesQuoteSaveAfterObserver */
    protected $object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->object = $this->objectManager->getObject(
            \Magento\Checkout\Observer\SalesQuoteSaveAfterObserver::class,
            ['checkoutSession' => $this->checkoutSession]
        );
    }

    public function testSalesQuoteSaveAfter()
    {
        $quoteId = 7;
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observer->expects($this->once())->method('getEvent')->will(
            $this->returnValue(new \Magento\Framework\DataObject(
                ['quote' => new \Magento\Framework\DataObject(['is_checkout_cart' => 1, 'id' => $quoteId])]
            ))
        );
        $this->checkoutSession->expects($this->once())->method('setQuoteId')->with($quoteId);

        $this->object->execute($observer);
    }
}
