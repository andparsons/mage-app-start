<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

/**
 * Extract styles from an object of form data to be used in the new mark up
 *
 * @api
 */
interface StyleExtractorInterface
{
    /**
     * Extract style and transform to string
     *
     * Given a map of style attributes from PageBuilder data object, we will convert specific attributes to PageBuilder
     * master format and output as an injectable string.
     *
     * @param array $formData
     * @param array $defaults
     * @param array $pluck
     * @return string
     */
    public function extractStyle(array $formData, array $defaults = [], array $pluck = []) : string;

    /**
     * Create a string of styles from an array
     *
     * @param array $styles
     *
     * @return string
     */
    public function createStyleFromArray(array $styles) : string;
}
