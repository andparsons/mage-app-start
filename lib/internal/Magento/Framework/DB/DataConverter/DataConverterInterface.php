<?php
namespace Magento\Framework\DB\DataConverter;

/**
 * Convert from one format to another
 */
interface DataConverterInterface
{
    /**
     * Convert from one format to another
     *
     * @param string $value
     * @return string
     *
     * @throws DataConversionException
     */
    public function convert($value);
}
