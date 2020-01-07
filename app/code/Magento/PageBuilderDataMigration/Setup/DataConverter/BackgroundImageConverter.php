<?php

declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Convert background images into their new JSON format
 */
class BackgroundImageConverter
{
    /**
     * Convert a background image into the new format
     *
     * @param string $backgroundImage
     * @param string $mobileImage
     *
     * @return string
     */
    public function convert($backgroundImage = null, $mobileImage = null) : ?string
    {
        $images = [];
        if ($backgroundImage) {
            $images['desktop_image'] = '{{media url=wysiwyg' . $backgroundImage . '}}';
        }
        if ($mobileImage) {
            $images['mobile_image'] = '{{media url=wysiwyg' . $mobileImage . '}}';
        }

        if (count($images) > 0) {
            // We have to special chars the JSON as it's placed within a HTML attribute
            return htmlspecialchars(json_encode($images));
        }

        return null;
    }
}
