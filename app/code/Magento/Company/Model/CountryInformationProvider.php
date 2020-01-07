<?php
namespace Magento\Company\Model;

/**
 * Provider of country information.
 */
class CountryInformationProvider
{
    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var array
     */
    private $countriesList;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    private $arrayUtils;

    /**
     * @var array
     */
    private $regionsList;

    /**
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Locale\ResolverInterface $resolver
     */
    public function __construct(
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Locale\ResolverInterface $resolver
    ) {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->arrayUtils = $arrayUtils;
        $this->resolver = $resolver;
    }

    /**
     * Retrieve full country name by country code.
     *
     * @param string $code
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryNameByCode($code)
    {
        $countryName = '';

        if ($code) {
            $country = $this->countryInformationAcquirer->getCountryInfo($code);

            if ($country && $country->getId()) {
                $countryName = $country->getFullNameLocale();
            }
        }

        return $countryName;
    }

    /**
     * Retrieve countries list from config.
     *
     * @return array
     */
    public function getCountriesList()
    {
        if ($this->countriesList !== null) {
            return $this->countriesList;
        }

        $this->countriesList = [];
        $countries = $this->countryInformationAcquirer->getCountriesInfo();

        if ($countries) {
            foreach ($countries as $country) {
                $this->countriesList[$country->getFullNameLocale()] = $country->getId();

                if ($country->getAvailableRegions()) {
                    $this->regionsList[$country->getId()] = $this->getRegionsData($country);
                }
            }
        }

        $this->arrayUtils->ksortMultibyte($this->countriesList, $this->resolver->getLocale());
        $this->countriesList = array_flip($this->countriesList);

        return $this->countriesList;
    }

    /**
     * Retrieve region name from country with $countryCode by $regionId and $regionName.
     *
     * @param string $countryCode
     * @param int $regionId
     * @param string $regionName
     * @return string
     */
    public function getActualRegionName($countryCode, $regionId, $regionName)
    {
        $regionsList = $this->getRegionsList();

        if (isset($regionsList[$countryCode]) && !empty($regionsList[$countryCode][$regionId])) {
            return $regionsList[$countryCode][$regionId];
        }

        return $regionName;
    }

    /**
     * Retrieve regions data for $country.
     *
     * @param \Magento\Directory\Api\Data\CountryInformationInterface $country
     * @return array
     */
    private function getRegionsData(\Magento\Directory\Api\Data\CountryInformationInterface $country)
    {
        $availableRegions = $country->getAvailableRegions();
        $regionsList = [];
        foreach ($availableRegions as $region) {
            $regionsList[$region->getId()] = $region->getName();
        }

        return $regionsList;
    }

    /**
     * Retrieve regions list for all countries.
     *
     * @return array
     */
    private function getRegionsList()
    {
        if ($this->regionsList !== null) {
            return $this->regionsList;
        }

        $countries = $this->countryInformationAcquirer->getCountriesInfo();

        if ($countries) {
            foreach ($countries as $country) {
                if ($country->getAvailableRegions()) {
                    $this->regionsList[$country->getId()] = $this->getRegionsData($country);
                }
            }
        }

        return $this->regionsList;
    }
}
