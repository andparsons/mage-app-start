<?php
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Timezone;

/*
 * Interface for converting localized date to UTC
 */
interface LocalizedDateToUtcConverterInterface
{
    /**
     * Convert localized date to UTC
     *
     * @param string $date
     * @return string
     */
    public function convertLocalizedDateToUtc($date);
}
