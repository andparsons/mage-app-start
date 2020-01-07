<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Email;

/**
 * Test for Magento\NegotiableQuote\Model\Email\LinkBuilder class.
 */
class LinkBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontendUrlBuilder;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendUrlBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\Email\LinkBuilder
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->frontendUrlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->backendUrlBuilder = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\NegotiableQuote\Model\Email\LinkBuilder::class,
            [
                'frontendUrlBuilder' => $this->frontendUrlBuilder,
                'backendUrlBuilder' => $this->backendUrlBuilder,
            ]
        );
    }

    /**
     * Test getBackendUrl method.
     *
     * @return void
     */
    public function testGetBackendUrl()
    {
        $url = 'http://example.com/admin';
        $this->backendUrlBuilder->expects($this->once())->method('getUrl')->with(null, [])->willReturn($url);

        $this->assertSame($url, $this->model->getBackendUrl());
    }

    /**
     * Test getFrontendUrl method.
     *
     * @return void
     */
    public function testGetFrontendUrl()
    {
        $routePath = 'http://example.com/';
        $scope = 'website';
        $store = 'default';
        $quoteId = 1;
        $this->frontendUrlBuilder->expects($this->once())->method('setScope')->with($scope)->willReturnSelf();
        $this->frontendUrlBuilder->expects($this->once())
            ->method('getUrl')
            ->with(
                $routePath,
                [
                    'quote_id' => $quoteId,
                    '_current' => false,
                    '_query' =>'___store=default'
                ]
            )
            ->willReturn($routePath . 'quote_id/1');

        $this->assertSame(
            $routePath . 'quote_id/1',
            $this->model->getFrontendUrl($routePath, $scope, $store, $quoteId)
        );
    }
}
