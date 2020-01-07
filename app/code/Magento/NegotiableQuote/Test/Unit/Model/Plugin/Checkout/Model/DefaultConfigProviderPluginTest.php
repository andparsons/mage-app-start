<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Checkout\Model;

/**
 * Class DefaultConfigProviderPluginTest
 * @package Magento\NegotiableQuote\Test\Unit\Model\Plugin\Checkout\Model
 */
class DefaultConfigProviderPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\DefaultConfigProviderPlugin
     */
    protected $plugin;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->context = $objectManager->getObject(\Magento\Framework\App\Action\Context::class);
        $this->urlBuilder = $this->context->getUrl();
        $this->urlBuilder->expects($this->any())->method('getUrl')->will($this->returnArgument(0));
        $this->plugin = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Plugin\Checkout\Model\DefaultConfigProviderPlugin::class,
            [
                'context' => $this->context,
                'urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * Test afterGetCheckoutUrl()
     *
     * @dataProvider afterGetCheckoutUrlDataProvider
     *
     * @param int $id
     * @param string $expectedResult
     */
    public function testAfterGetCheckoutUrl($id, $expectedResult)
    {
        $this->context->getRequest()->expects($this->any())->method('getParam')->willReturn($id);
        $subject = $this->createMock(\Magento\Checkout\Model\DefaultConfigProvider::class);

        $this->assertEquals($expectedResult, $this->plugin->afterGetCheckoutUrl($subject, 'url'));
    }

    /**
     * @return array
     */
    public function afterGetCheckoutUrlDataProvider()
    {
        return [
            [0, 'url'],
            [2, 'checkout']
        ];
    }

    /**
     * Test afterGetDefaultSuccessPageUrl()
     *
     * @dataProvider afterGetDefaultSuccessPageUrlDataProvider
     *
     * @param int $id
     * @param string $expectedResult
     */
    public function testAfterGetDefaultSuccessPageUrl($id, $expectedResult)
    {
        $this->context->getRequest()->expects($this->any())->method('getParam')->willReturn($id);
        $subject = $this->createMock(\Magento\Checkout\Model\DefaultConfigProvider::class);

        $this->assertEquals($expectedResult, $this->plugin->afterGetDefaultSuccessPageUrl($subject, 'url'));
    }

    /**
     * @return array
     */
    public function afterGetDefaultSuccessPageUrlDataProvider()
    {
        return [
            [0, 'url'],
            [2, 'negotiable_quote/quote/order']
        ];
    }
}
