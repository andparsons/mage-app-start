<?php
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Configured postcode validation patterns
 */
interface ConfigInterface
{
    /**
     * Returns array of postcodes validation patterns
     *
     * @return array
     */
    public function getPostCodes();
}
