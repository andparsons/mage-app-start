<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Checkout;

/**
 * Class LinkTest.
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var  \Magento\NegotiableQuote\Block\Checkout\Link
     */
    private $block;

    /**
     * @var  \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var  \Magento\NegotiableQuote\Helper\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHelper;

    /**
     * @var  \Magento\Quote\Api\CartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartManagement;

    /**
     * @var  \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteHelper;

    /**
     * @var  \Magento\Framework\File\Size|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSize;

    /**
     * @var  \Magento\NegotiableQuote\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteConfig;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->userContext = $this->getMockForAbstractClass(
            \Magento\Authorization\Model\UserContextInterface::class,
            [],
            '',
            false
        );

        $this->configHelper = $this->createMock(
            \Magento\NegotiableQuote\Helper\Config::class
        );

        $this->cartManagement = $this->getMockForAbstractClass(
            \Magento\Quote\Api\CartManagementInterface::class,
            [],
            '',
            false
        );

        $this->quoteHelper = $this->createMock(
            \Magento\NegotiableQuote\Helper\Quote::class
        );

        $this->fileSize = $this->createMock(
            \Magento\Framework\File\Size::class
        );

        $this->negotiableQuoteConfig = $this->createMock(
            \Magento\NegotiableQuote\Model\Config::class
        );

        $this->authorization = $this->createMock(
            \Magento\Company\Api\AuthorizationInterface::class
        );

        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Checkout\Link::class,
            [
                'userContext' => $this->userContext,
                'configHelper' => $this->configHelper,
                'cartManagement' => $this->cartManagement,
                'quoteHelper' => $this->quoteHelper,
                'fileSize' => $this->fileSize,
                'negotiableQuoteConfig' => $this->negotiableQuoteConfig,
                'authorization' => $this->authorization,
                'data' => [],
                '_urlBuilder' => $this->urlBuilder,
            ]
        );
    }

    /**
     * Test isQuoteRequestAllowed.
     *
     * @param bool $result
     * @return void
     * @dataProvider isQuoteRequestAllowedDataProvider
     */
    public function testIsQuoteRequestAllowed($result)
    {
        $userId = 1;
        $this->userContext->expects($this->once())->method('getUserId')->willReturn($userId);

        $quote = $this->createMock(
            \Magento\Quote\Model\Quote::class
        );
        $this->cartManagement->expects($this->once())->method('getCartForCustomer')
            ->with($userId)->willReturn($quote);
        $this->configHelper->expects($this->once())->method('isQuoteAllowed')
            ->with($quote)->willReturn($result);
        $this->authorization->expects($this->any())->method('isAllowed')->willReturn($result);

        $this->assertEquals($result, $this->block->isQuoteRequestAllowed());
    }

    /**
     * DataProvider for isQuoteRequestAllowed.
     *
     * @return array
     */
    public function isQuoteRequestAllowedDataProvider()
    {
        return [
            [true], [false]
        ];
    }

    /**
     * Test for method getDisallowMessage.
     *
     * @return void
     */
    public function testGetDisallowMessage()
    {
        $string = '';
        $this->configHelper->expects($this->once())->method('getMinimumAmountMessage')
            ->willReturn($string);

        $this->assertEquals($string, $this->block->getDisallowMessage());
    }

    /**
     * Test for method getCreateNegotiableQuoteUrl.
     *
     * @return void
     */
    public function testGetCreateNegotiableQuoteUrl()
    {
        $path = 'negotiable_quote/quote/create';
        $url = 'http://example.com/';

        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->willReturn($url . $path);
        $this->assertEquals($url . $path, $this->block->getCreateNegotiableQuoteUrl());
    }

    /**
     * Test for method getCheckQuoteDiscountsUrl.
     *
     * @return void
     */
    public function testGetCheckQuoteDiscountsUrl()
    {
        $path = 'negotiable_quote/quote/checkDiscount';
        $url = 'http://example.com/';

        $this->urlBuilder->expects($this->once())->method('getUrl')
            ->willReturn($url . $path);
        $this->assertEquals($url . $path, $this->block->getCreateNegotiableQuoteUrl());
    }

    /**
     * Test for method getMaxFileSize.
     *
     * @return void
     */
    public function testGetMaxFileSize()
    {
        $maxFileSize = 14;
        $this->fileSize->expects($this->once())->method('convertSizeToInteger')
            ->willReturn($maxFileSize);

        $this->assertEquals($maxFileSize, $this->block->getMaxFileSize());
    }

    /**
     * Test for method getAllowedExtensionsString.
     *
     * @return void
     */
    public function testGetAllowedExtensionsString()
    {
        $extensions = '.php.inc';
        $this->negotiableQuoteConfig->expects($this->once())->method('getAllowedExtensions')
            ->willReturn($extensions);

        $this->assertEquals($extensions, $this->block->getAllowedExtensions());
    }

    /**
     * Test for method getAllowedExtensionsNull.
     *
     * @return void
     */
    public function testGetAllowedExtensionsNull()
    {
        $extensions = null;
        $this->negotiableQuoteConfig->expects($this->once())->method('getAllowedExtensions')
            ->willReturn($extensions);

        $this->assertEquals($extensions, $this->block->getAllowedExtensions());
    }

    /**
     * Test for method getMaxFileSizeMbConfigSize.
     *
     * @return void
     */
    public function testGetMaxFileSizeMbConfigSize()
    {
        $maxFileSizeConfig = 10;
        $maxFileSize = 14;
        $this->negotiableQuoteConfig->expects($this->once())->method('getMaxFileSize')
            ->willReturn($maxFileSizeConfig);
        $this->fileSize->expects($this->once())->method('getMaxFileSizeInMb')
            ->willReturn($maxFileSize);

        $this->assertEquals($maxFileSizeConfig, $this->block->getMaxFileSizeMb());
    }

    /**
     * Test for method getMaxFileSizeMbFileSize.
     *
     * @return void
     */
    public function testGetMaxFileSizeMbFileSize()
    {
        $maxFileSizeConfig = null;
        $maxFileSize = 14;
        $this->negotiableQuoteConfig->expects($this->once())->method('getMaxFileSize')
            ->willReturn($maxFileSizeConfig);
        $this->fileSize->expects($this->once())->method('getMaxFileSizeInMb')
            ->willReturn($maxFileSize);

        $this->assertEquals($maxFileSize, $this->block->getMaxFileSizeMb());
    }
}
