<?php
namespace Magento\Customer\Test\Unit\Block\Account;

class LinkTest extends \PHPUnit\Framework\TestCase
{
    public function testGetHref()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $helper = $this->getMockBuilder(
            \Magento\Customer\Model\Url::class
        )->disableOriginalConstructor()->setMethods(
            ['getAccountUrl']
        )->getMock();
        $layout = $this->getMockBuilder(
            \Magento\Framework\View\Layout::class
        )->disableOriginalConstructor()->setMethods(
            ['helper']
        )->getMock();

        $block = $objectManager->getObject(
            \Magento\Customer\Block\Account\Link::class,
            ['layout' => $layout, 'customerUrl' => $helper]
        );
        $helper->expects($this->any())->method('getAccountUrl')->will($this->returnValue('account url'));

        $this->assertEquals('account url', $block->getHref());
    }
}
