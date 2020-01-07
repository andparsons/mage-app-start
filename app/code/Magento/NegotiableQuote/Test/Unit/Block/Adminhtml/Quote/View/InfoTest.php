<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Unit\Block\Adminhtml\Quote\View;

/**
 * Unit test for Info.
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Info
     */
    private $info;

    /**
     * @var \Magento\NegotiableQuote\Helper\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    private $negotiableQuoteHelper;

    /**
     * @var \Magento\NegotiableQuote\Model\Company\DetailsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyDetailsProvider;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilder;

    /**
     * @var \Magento\NegotiableQuote\Model\Creator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $creatorMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->companyDetailsProvider = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Model\Company\DetailsProvider::class)
            ->setMethods(
                [
                    'getCompany',
                    'getCompanyName',
                    'getCompanyAdminEmail',
                    'getCompanyAdmin',
                    'existsSalesRepresentative',
                    'getQuoteOwnerName'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilder = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->negotiableQuoteHelper = $this->getMockBuilder(\Magento\NegotiableQuote\Helper\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creatorMock = $this->getMockBuilder(\Magento\NegotiableQuote\Model\Creator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->info = $objectManagerHelper->getObject(
            \Magento\NegotiableQuote\Block\Adminhtml\Quote\View\Info::class,
            [
                'companyDetailsProvider' => $this->companyDetailsProvider,
                'negotiableQuoteHelper' => $this->negotiableQuoteHelper,
                '_urlBuilder' => $this->urlBuilder,
                'creator' => $this->creatorMock
            ]
        );
    }

    /**
     * Test existsCompany method.
     *
     * @return void
     */
    public function testExistsCompany()
    {
        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->companyDetailsProvider->expects($this->once())->method('getCompany')->willReturn($company);

        $this->assertTrue($this->info->existsCompany());
    }

    /**
     * Test getCompanyAdminEmail method.
     *
     * @return void
     */
    public function testGetCompanyAdminEmail()
    {
        $adminEmail = 'admin@test.com';
        $this->companyDetailsProvider->expects($this->once())->method('getCompanyAdminEmail')->willReturn($adminEmail);
        $result = $this->info->getCompanyAdminEmail();

        $this->assertEquals($result, $adminEmail);
    }

    /**
     * Test getCompanyName method.
     *
     * @return void
     */
    public function testGetCompanyName()
    {
        $companyName = 'Test Company';
        $this->companyDetailsProvider->expects($this->once())->method('getCompanyName')->willReturn($companyName);

        $this->assertEquals('Test Company', $this->info->getCompanyName());
    }

    /**
     * Test getCompanyUrl.
     *
     * @return void
     */
    public function testGetCompanyUrl()
    {
        $path = 'company/index/edit';
        $url = 'http://example.com/';
        $companyId = 1;

        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $company->expects($this->once())->method('getId')->willReturn($companyId);
        $this->companyDetailsProvider->expects($this->exactly(2))->method('getCompany')->willReturn($company);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('company/index/edit', ['id' => $companyId])
            ->willReturn($url . $path . '/id/' . $companyId . '/');

        $this->assertEquals($url . $path . '/id/' . $companyId . '/', $this->info->getCompanyUrl());
    }

    /**
     * Test getCompanyAdminUrl method.
     *
     * @return void
     */
    public function testGetCompanyAdminUrl()
    {
        $path = 'company/index/edit';
        $url = 'http://example.com/';
        $adminId = 1;

        $this->companyDetailsProvider->expects($this->once())
            ->method('getCompanyAdmin')->willReturn(['customer_id' => $adminId]);
        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('customer/index/edit', ['id' => $adminId])
            ->willReturn($url . $path . '/id/' . $adminId . '/');

        $this->assertEquals($url . $path . '/id/' . $adminId . '/', $this->info->getCompanyAdminUrl());
    }

    /**
     * Test getSalesRepUrl method.
     *
     * @return void
     */
    public function testGetSalesRepUrl()
    {
        $path = 'adminhtml/user/edit';
        $url = 'http://example.com/';
        $userId = 1;

        $company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->setMethods(['getSalesRepresentativeId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->companyDetailsProvider->expects($this->exactly(2))->method('getCompany')->willReturn($company);
        $company->expects($this->exactly(1))->method('getSalesRepresentativeId')->willReturn($userId);

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/user/edit', ['user_id' => $userId])
            ->willReturn($url . $path . '/user_id/' . $userId . '/');

        $this->assertEquals($url . $path . '/user_id/' . $userId . '/', $this->info->getSalesRepUrl());
    }

    /**
     * Test existsSalesRepresentative method.
     *
     * @return void
     */
    public function testExistsSalesRepresentative()
    {
        $expectedResult = true;
        $this->companyDetailsProvider->expects($this->once())
                ->method('existsSalesRepresentative')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->info->existsSalesRepresentative());
    }

    /**
     * @return void
     */
    public function testGetQuoteOwnerFullName()
    {
        $quoteOwnerName = 'Peter Parker';
        $creatorName = 'John Doe';
        $creatorType = \Magento\Authorization\Model\UserContextInterface::USER_TYPE_ADMIN;
        $creatorId = 1;
        $quoteId = 1;

        $this->companyDetailsProvider->expects($this->once())->method('getQuoteOwnerName')->willReturn($quoteOwnerName);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->negotiableQuoteHelper->expects($this->any())->method('resolveCurrentQuote')->willReturn($quoteMock);
        $extensionAttributesMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNegotiableQuote'])
            ->getMockForAbstractClass();
        $quoteMock->expects($this->any())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $negotiableQuoteMock = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCreatorType', 'getCreatorId', 'getQuoteId'])
            ->getMockForAbstractClass();
        $extensionAttributesMock->expects($this->any())->method('getNegotiableQuote')->willReturn($negotiableQuoteMock);
        $negotiableQuoteMock->expects($this->any())->method('getCreatorType')
            ->willReturn($creatorType);
        $negotiableQuoteMock->expects($this->once())->method('getCreatorId')
            ->willReturn($creatorId);
        $negotiableQuoteMock->expects($this->once())->method('getQuoteId')
            ->willReturn($quoteId);
        $this->creatorMock->expects($this->once())
            ->method('retrieveCreatorName')->with($creatorType, $creatorId, $quoteId)->willReturn($creatorName);
        $result = __(
            '%creator (for %customer)',
            ['creator' => $creatorName, 'customer' => $quoteOwnerName]
        );

        $this->assertEquals($result, $this->info->getQuoteOwnerFullName());
    }
}
