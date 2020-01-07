<?php

namespace Magento\NegotiableQuote\Test\Unit\Model\Plugin\Company\Model;

/**
 * Class DataProviderTest.
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\NegotiableQuote\Model\Plugin\Company\Model\DataProvider
     */
    private $dataProvider;

    /**
     * @var \Magento\NegotiableQuote\Helper\Company|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyHelper;

    /**
     * @var \Magento\Company\Model\Company\DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $companyDataProvider;

    /**
     * Set up.
     */
    protected function setUp()
    {
        $this->companyHelper = $this->createMock(\Magento\NegotiableQuote\Helper\Company::class);

        $this->companyDataProvider =
            $this->createPartialMock(\Magento\Company\Model\Company\DataProvider::class, ['save']);

        $this->dataProvider = new \Magento\NegotiableQuote\Model\Plugin\Company\Model\DataProvider(
            $this->companyHelper
        );
    }

    /**
     * Test for method aroundGetSettingsData.
     *
     * @return void
     */
    public function testAroundGetSettingsData()
    {
        $quoteConfig = $this->createMock(\Magento\NegotiableQuote\Api\Data\CompanyQuoteConfigInterface::class);
        $this->companyHelper
            ->expects($this->any())->method('getQuoteConfig')
            ->will($this->returnValue($quoteConfig));

        $company = $this->getMockForAbstractClass(
            \Magento\Company\Api\Data\CompanyInterface::class,
            [],
            '',
            false
        );
        $proceed = function () use ($company) {
            return [$company];
        };
        $quoteConfig->expects($this->any())->method('getIsQuoteEnabled')->willReturn(true);

        $this->dataProvider->aroundGetSettingsData($this->companyDataProvider, $proceed, $company);
    }
}
