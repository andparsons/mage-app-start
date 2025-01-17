<?php
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Interface \Magento\Directory\Model\Country\Postcode\ValidatorInterface
 *
 */
interface ValidatorInterface
{
    /**
     * Validate postcode for selected country by mask
     *
     * @param string $postcode
     * @param string $countryId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function validate($postcode, $countryId);
}
