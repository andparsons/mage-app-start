<?php
namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UnsetAllObserverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Observer\UnsetAllObserver */
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
            \Magento\Checkout\Observer\UnsetAllObserver::class,
            ['checkoutSession' => $this->checkoutSession]
        );
    }

    public function testUnsetAll()
    {
        $this->checkoutSession->expects($this->once())->method('clearQuote')->will($this->returnSelf());
        $this->checkoutSession->expects($this->once())->method('clearStorage')->will($this->returnSelf());

        $observerMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object->execute($observerMock);
    }
}
