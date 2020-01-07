<?php

namespace Magento\NegotiableQuote\Test\Unit\Helper;

/**
 * Test for \Magento\NegotiableQuote\Helper\Company class.
 */
class CompanyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Helper\Company
     */
    private $helper;

    /**
     * @var \Magento\Company\Api\Data\CompanyExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyExtensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $quoteConfigManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = $objectManagerHelper->getConstructArguments(\Magento\NegotiableQuote\Helper\Company::class);
        $this->companyExtensionFactoryMock =
            $this->createPartialMock(\Magento\Company\Api\Data\CompanyExtensionFactory::class, ['create']);
        $this->quoteConfigManager = $this->createPartialMock(
            \Magento\NegotiableQuote\Model\CompanyQuoteConfigManagement::class,
            ['getByCompanyId']
        );

        $arguments['quoteConfigManager'] = $this->quoteConfigManager;
        $arguments['companyExtensionFactory'] = $this->companyExtensionFactoryMock;

        $this->helper =
            $objectManagerHelper->getObject(\Magento\NegotiableQuote\Helper\Company::class, $arguments);
    }

    /**
     * Test for loadQuoteConfig.
     *
     * @return void
     */
    public function testLoadQuoteConfig()
    {
        $companyMock = $this->createPartialMock(
            \Magento\Company\Model\Company::class,
            ['setExtensionAttributes', 'getExtensionAttributes']
        );

        $companyExtensionMock = $this
            ->getMockBuilder(\Magento\Company\Api\Data\CompanyExtensionInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteConfig', 'setQuoteConfig'])
            ->getMockForAbstractClass();

        $companyQuoteConfigMock = $this
            ->getMockBuilder(\Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteConfig', 'setQuoteConfig'])
            ->getMockForAbstractClass();

        $companyExtensionMock
            ->expects($this->at(0))
            ->method('getQuoteConfig')
            ->will($this->returnValue(null));

        $companyExtensionMock->expects($this->at(1))->method('getQuoteConfig')->will(
            $this->returnValue($companyQuoteConfigMock)
        );
        $this->quoteConfigManager
            ->expects($this->any())
            ->method('getByCompanyId')
            ->will($this->returnValue($companyQuoteConfigMock));

        $companyMock
            ->expects($this->at(0))
            ->method('getExtensionAttributes')
            ->will($this->returnValue($companyExtensionMock));
        $companyMock->expects($this->at(1))->method('getExtensionAttributes')->will(
            $this->returnValue($companyExtensionMock)
        );

        $company = $this->helper->loadQuoteConfig($companyMock);

        $this->assertInstanceOf(\Magento\Company\Api\Data\CompanyInterface::class, $company);
    }
}
