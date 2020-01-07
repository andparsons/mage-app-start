<?php

namespace Magento\Company\Test\Unit\Model\SaveValidator;

/**
 * Unit test for company address validator.
 */
class CompanyAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Company\Api\Data\CompanyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $company;

    /**
     * @var \Magento\Framework\Exception\InputException|\PHPUnit_Framework_MockObject_MockObject
     */
    private $exception;

    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $countryInformationAcquirer;

    /**
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryData;

    /**
     * @var \Magento\Company\Model\SaveValidator\CompanyAddress
     */
    private $companyAddress;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->company = $this->getMockBuilder(\Magento\Company\Api\Data\CompanyInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->exception = $this->getMockBuilder(\Magento\Framework\Exception\InputException::class)
            ->disableOriginalConstructor()->getMock();
        $this->countryInformationAcquirer = $this
            ->getMockBuilder(\Magento\Directory\Api\CountryInformationAcquirerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->directoryData = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->companyAddress = $objectManager->getObject(
            \Magento\Company\Model\SaveValidator\CompanyAddress::class,
            [
                'company' => $this->company,
                'exception' => $this->exception,
                'countryInformationAcquirer' => $this->countryInformationAcquirer,
                'directoryData' => $this->directoryData
            ]
        );
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $countryId = 'US';
        $regionId = 11;
        $this->company->expects($this->atLeastOnce())->method('getCountryId')->willReturn($countryId);
        $countryInformation = $this->getMockBuilder(\Magento\Directory\Api\Data\CountryInformationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->directoryData->expects($this->once())->method('isShowNonRequiredState')->willReturn(true);
        $this->countryInformationAcquirer->expects($this->once())
            ->method('getCountryInfo')->with($countryId)->willReturn($countryInformation);
        $region = $this->getMockBuilder(\Magento\Directory\Api\Data\RegionInformationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $countryInformation->expects($this->once())->method('getAvailableRegions')->willReturn([$region]);
        $this->company->expects($this->atLeastOnce())->method('getRegionId')->willReturn($regionId);
        $region->expects($this->once())->method('getId')->willReturn($regionId);
        $this->exception->expects($this->never())->method('addError');
        $this->companyAddress->execute();
    }

    /**
     * Test for execute method with non-existing region.
     *
     * @return void
     */
    public function testExecuteWithNonExistingRegion()
    {
        $countryId = 'US';
        $regionId = 11;
        $this->company->expects($this->atLeastOnce())->method('getCountryId')->willReturn($countryId);
        $countryInformation = $this->getMockBuilder(\Magento\Directory\Api\Data\CountryInformationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->directoryData->expects($this->once())->method('isShowNonRequiredState')->willReturn(true);
        $this->countryInformationAcquirer->expects($this->once())
            ->method('getCountryInfo')->with($countryId)->willReturn($countryInformation);
        $region = $this->getMockBuilder(\Magento\Directory\Api\Data\RegionInformationInterface::class)
            ->disableOriginalConstructor()->getMock();
        $countryInformation->expects($this->once())->method('getAvailableRegions')->willReturn([$region]);
        $this->company->expects($this->atLeastOnce())->method('getRegionId')->willReturn($regionId);
        $region->expects($this->once())->method('getId')->willReturn(12);
        $this->exception->expects($this->once())->method('addError')->with(
            __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'region_id', 'value' => $regionId]
            )
        )->willReturnSelf();
        $this->companyAddress->execute();
    }

    /**
     * Test for execute method with non-existing country.
     *
     * @return void
     */
    public function testExecuteWithNonExistingCountry()
    {
        $countryId = 'US';
        $this->company->expects($this->atLeastOnce())->method('getCountryId')->willReturn($countryId);
        $this->directoryData->expects($this->once())->method('isShowNonRequiredState')->willReturn(true);
        $this->countryInformationAcquirer->expects($this->once())->method('getCountryInfo')->with($countryId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $this->exception->expects($this->once())->method('addError')->with(
            __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'country_id', 'value' => $countryId]
            )
        )->willReturnSelf();
        $this->companyAddress->execute();
    }
}
