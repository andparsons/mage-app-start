<?php
namespace Magento\Wishlist\Test\Unit\Observer;

use \Magento\Wishlist\Observer\CustomerLogout as Observer;

class CustomerLogoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    protected function setUp()
    {
        $this->customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setWishlistItemCount', 'isLoggedIn', 'getCustomerId'])
            ->getMock();

        $this->observer = new Observer(
            $this->customerSession
        );
    }

    public function testExecute()
    {
        $event = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $event \Magento\Framework\Event\Observer */

        $this->customerSession->expects($this->once())
            ->method('setWishlistItemCount')
            ->with($this->equalTo(0));

        $this->observer->execute($event);
    }
}
