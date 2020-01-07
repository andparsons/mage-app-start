<?php

namespace Magento\NegotiableQuote\Test\Unit\Block\Quote\Info;

use Magento\Company\Api\Data\CompanyInterface;

/**
 * Class LinksTest.
 */
class LinksTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Quote\Info\Links
     */
    private $link;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyManagement;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userContext;

    /**
     * @var \Magento\Company\Api\AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorization;

    /**
     * @var int
     */
    private $quoteId;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->quoteId = 1;
        $this->urlBuilder = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);

        $quote = $this->getMockForAbstractClass(\Magento\Quote\Api\Data\CartInterface::class);
        $quote->expects($this->any())->method('getId')->will($this->returnValue($this->quoteId));

        $this->negotiableQuoteHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Quote::class);
        $this->negotiableQuoteHelper->expects($this->any())
            ->method('resolveCurrentQuote')
            ->willReturn($quote);
        $this->userContext = $this->createMock(\Magento\Authorization\Model\UserContextInterface::class);
        $this->authorization = $this->createMock(\Magento\Company\Api\AuthorizationInterface::class);
        $this->companyManagement  = $this
            ->getMockBuilder(\Magento\Company\Api\CompanyManagementInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getByCustomerId'])
            ->getMockForAbstractClass();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->link = $objectManager->getObject(
            \Magento\NegotiableQuote\Block\Quote\Info\Links::class,
            [
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                'authorization' => $this->authorization,
                '_urlBuilder' => $this->urlBuilder,
                'companyManagement' => $this->companyManagement,
                'userContext' => $this->userContext
            ]
        );

        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->link->setLayout($layout);
    }

    /**
     * Test getDeleteUrl.
     *
     * @return void
     */
    public function testGetDeleteUrl()
    {
        $path = 'negotiable_quote/quote/delete';
        $url = 'http://example.com/';

        $this->urlBuilder->expects($this->any())
            ->method('getUrl')->will(
                $this->returnValue($url . $path . '/quote_id/' . $this->quoteId . '/')
            );

        $this->assertEquals($url . $path . '/quote_id/1/', $this->link->getDeleteUrl());
    }

    /**
     * Test getPrintUrl.
     *
     * @return void
     */
    public function testGetPrintUrl()
    {
        $path = 'negotiable_quote/quote/print';
        $url = 'http://example.com/';

        $this->urlBuilder->expects($this->any())
            ->method('getUrl')->will(
                $this->returnValue($url . $path . '/quote_id/' . $this->quoteId . '/')
            );

        $this->assertEquals($url . $path . '/quote_id/1/', $this->link->getPrintUrl());
    }

    /**
     * Test isCheckoutLinkVisible.
     *
     * @param bool $expectedResult
     * @param int $companyStatus
     * @dataProvider isCheckoutLinkVisibleDataProvider
     */
    public function testIsCheckoutLinkVisible($expectedResult, $companyStatus)
    {
        $company  = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus'])
            ->getMockForAbstractClass();
        $this->userContext->expects($this->any())->method('getUserId')->willReturn(1);
        $this->companyManagement->expects($this->once())->method('getByCustomerId')->with(1)->willReturn($company);
        $company->expects($this->once())->method('getStatus')->willReturn($companyStatus);
        $this->authorization->expects($this->once())->method('isAllowed')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->link->isCheckoutLinkVisible());
    }

    /**
     * Data provider isCheckoutLinkVisible
     *
     * @return array
     */
    public function isCheckoutLinkVisibleDataProvider()
    {
        return [
            [false, CompanyInterface::STATUS_BLOCKED],
            [true,  CompanyInterface::STATUS_APPROVED]
        ];
    }
}
