<?php

namespace Magento\NegotiableQuote\Test\Unit\Plugin\Customer\Block\Address;

/**
 * Class EditPluginTest
 */
class EditPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\NegotiableQuote\Plugin\Customer\Block\Address\EditPlugin
     */
    private $editPlugin;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->editPlugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Plugin\Customer\Block\Address\EditPlugin::class,
            [
                'urlBuilder' => $this->urlBuilder
            ]
        );
    }

    /**
     * Test afterGetSaveUrl
     */
    public function testAfterGetSaveUrl()
    {
        $address = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
        $address->expects($this->any())->method('getId')->willReturn(1);
        /**
         * @var \Magento\Customer\Block\Address\Edit|\PHPUnit_Framework_MockObject_MockObject $subject
         */
        $subject = $this->createMock(\Magento\Customer\Block\Address\Edit::class);
        $subject->expects($this->any())->method('getAddress')->willReturn($address);
        $url = 'url';
        $this->urlBuilder->expects($this->any())->method('getUrl')->willReturn($url);

        $this->assertEquals($url, $this->editPlugin->afterGetSaveUrl($subject, ''));
    }
}
