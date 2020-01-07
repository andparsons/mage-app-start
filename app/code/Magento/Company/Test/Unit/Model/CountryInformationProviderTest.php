<?php

namespace Magento\Company\Test\Unit\Model;

/**
 * Class CountryInformationProviderTest
 */
class CountryInformationProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryInformationAcquirer;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayUtils;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    /**
     * @var \Magento\Company\Model\CountryInformationProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryInformationProvider;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->countryInformationAcquirer = $this->createMock(
            \Magento\Directory\Api\CountryInformationAcquirerInterface::class
        );
        $this->arrayUtils = $this->createMock(
            \Magento\Framework\Stdlib\ArrayUtils::class
        );
        $this->resolver = $this->createMock(
            \Magento\Framework\Locale\ResolverInterface::class
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->countryInformationProvider = $objectManager->getObject(
            \Magento\Company\Model\CountryInformationProvider::class,
            [
                'countryInformationAcquirer' => $this->countryInformationAcquirer,
                'arrayUtils' => $this->arrayUtils,
                'resolver' => $this->resolver
            ]
        );
    }

    /**
     * Test getCountryNameByCode
     *
     * @param string $code
     * @param string $countryName
     * @dataProvider dataProviderGetCountryNameByCode
     */
    public function testGetCountryNameByCode($code, $countryName)
    {
        $countryInfo =
            $this->createMock(\Magento\Directory\Api\Data\CountryInformationInterface::class);
        $countryInfo->expects($this->any())->method('getId')->willReturn($code);
        $countryInfo->expects($this->any())->method('getFullNameLocale')->willReturn($countryName);
        $this->countryInformationAcquirer->expects($this->any())->method('getCountryInfo')->willReturn($countryInfo);

        $this->assertEquals($countryName, $this->countryInformationProvider->getCountryNameByCode($code));
    }

    /**
     * Test getCountriesList
     *
     * @param array $countriesList
     * @dataProvider dataProviderGetCountriesList
     */
    public function testGetCountriesList(array $countriesList)
    {
        $this->populateCountriesAndRegions();
        $this->resolver->expects($this->any())->method('getLocale')->willReturn('en_US');
        $this->arrayUtils->expects($this->any())->method('ksortMultibyte')->willReturn($countriesList);

        $this->assertEquals($countriesList, $this->countryInformationProvider->getCountriesList());
    }

    /**
     * Test getActualRegionName
     *
     * @param string $countryCode
     * @param int $regionId
     * @param string $regionName
     * @dataProvider dataProviderGetActualRegionName
     */
    public function testGetActualRegionName($countryCode, $regionId, $regionName)
    {
        $this->populateCountriesAndRegions();

        $this->assertEquals(
            $regionName,
            $this->countryInformationProvider->getActualRegionName($countryCode, $regionId, $regionName)
        );
    }

    /**
     * Data provider getCountryNameByCode
     *
     * @return array
     */
    public function dataProviderGetCountryNameByCode()
    {
        return [
            ['US', 'United States']
        ];
    }

    /**
     * Data provider getRegionNameById
     *
     * @return array
     */
    public function dataProviderGetRegionNameById()
    {
        return [
            ['1', 'Alabama']
        ];
    }

    /**
     * Data provider getCountriesList
     *
     * @return array
     */
    public function dataProviderGetCountriesList()
    {
        return [
            [
                ['US' => 'United States', 'UK' => 'United Kingdom']
            ]
        ];
    }

    /**
     * Data provider getActualRegionId
     *
     * @return array
     */
    public function dataProviderGetActualRegionId()
    {
        return [
            [12, 12],
            [null, 1]
        ];
    }

    /**
     * Data provider getActualRegionName
     *
     * @return array
     */
    public function dataProviderGetActualRegionName()
    {
        return [
            ['US', '1', 'Alabama'],
            ['UK', '1', 'London']
        ];
    }

    /**
     * populateCountriesAndRegions
     */
    private function populateCountriesAndRegions()
    {
        $firstCountry = $this->createMock(
            \Magento\Directory\Api\Data\CountryInformationInterface::class
        );
        $secondCountry = $this->createMock(
            \Magento\Directory\Api\Data\CountryInformationInterface::class
        );
        $firstRegion = $this->createMock(
            \Magento\Directory\Api\Data\RegionInformationInterface::class
        );
        $secondRegion = $this->createMock(
            \Magento\Directory\Api\Data\RegionInformationInterface::class
        );
        $firstRegion->expects($this->any())->method('getId')->willReturn(1);
        $firstRegion->expects($this->any())->method('getName')->willReturn('Alabama');
        $secondRegion->expects($this->any())->method('getId')->willReturn(12);
        $secondRegion->expects($this->any())->method('getName')->willReturn('California');
        $regionsIterator = new \ArrayIterator([$firstRegion, $secondRegion]);
        $firstCountry->expects($this->any())->method('getId')->willReturn('US');
        $firstCountry->expects($this->any())->method('getFullNameLocale')->willReturn('United States');
        $firstCountry->expects($this->any())->method('getAvailableRegions')->willReturn($regionsIterator);
        $secondCountry->expects($this->any())->method('getId')->willReturn('UK');
        $secondCountry->expects($this->any())->method('getFullNameLocale')->willReturn('United Kingdom');
        $secondCountry->expects($this->any())->method('getAvailableRegions')->willReturn(null);
        $countriesIterator = new \ArrayIterator([$firstCountry, $secondCountry]);
        $this->countryInformationAcquirer->expects($this->any())->method('getCountriesInfo')
            ->willReturn($countriesIterator);
    }
}
