<?php

namespace Magento\Wishlist\Test\Unit\Observer;

use \Magento\Wishlist\Observer\CustomerLogin as Observer;

class CustomerLoginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Observer
     */
    protected $observer;

    /**
     * @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(\Magento\Wishlist\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observer = new Observer($this->helper);
    }

    public function testExecute()
    {
        $event = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var $event \Magento\Framework\Event\Observer */

        $this->helper->expects($this->once())
            ->method('calculate');

        $this->observer->execute($event);
    }
}
