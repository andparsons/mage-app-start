<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Class ColorConverter
 */
class ColorConverter
{
    /**
     * Convert a hex value into it's corresponding RGB values
     *
     * @param string $hex
     *
     * @return string
     */
    public function convert(string $hex) : string
    {
        list($r, $g, $b) = sscanf(ltrim($hex, '#'), "%02x%02x%02x");
        return "rgb($r, $g, $b)";
    }
}
