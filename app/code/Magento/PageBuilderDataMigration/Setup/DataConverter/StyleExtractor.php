<?php
declare(strict_types=1);

namespace Magento\PageBuilderDataMigration\Setup\DataConverter;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Extract and convert styles from a data array into value style string
 *
 * @api
 */
class StyleExtractor implements StyleExtractorInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var ColorConverter
     */
    private $colorConverter;

    /**
     * @var array
     */
    private $marginStyles = [
        'margin-top',
        'margin-left',
        'margin-bottom',
        'margin-left'
    ];

    /**
     * @var array
     */
    private $paddingStyles = [
        'padding-top',
        'padding-left',
        'padding-bottom',
        'padding-left'
    ];

    /**
     * @param Json $serializer
     * @param ColorConverter $colorConverter
     */
    public function __construct(
        Json $serializer,
        ColorConverter $colorConverter
    ) {
        $this->serializer = $serializer;
        $this->colorConverter = $colorConverter;
    }

    /**
     * @inheritdoc
     */
    public function extractStyle(array $formData, array $defaults = [], array $pluck = []) : string
    {
        // Extract advanced styles and margins and paddings
        $styleAttributes = $this->extractAdvancedStyles($formData);
        $styleAttributes = $this->extractMarginPadding($formData, $styleAttributes, $defaults);

        // Set defaults onto the style attributes
        $styleAttributes = $this->setDefaults($styleAttributes, $defaults);

        // Include the specific margin and padding items in the pluck array
        if (in_array('margin', $pluck) && !isset($styleAttributes['margin'])) {
            $pluck = array_merge(
                $pluck,
                $this->marginStyles
            );
        }
        if (in_array('padding', $pluck) && !isset($styleAttributes['padding'])) {
            $pluck = array_merge(
                $pluck,
                $this->paddingStyles
            );
        }

        // We can define an array of styles to pluck from the overall set
        if (!empty($pluck)) {
            $styleAttributes = $this->pluckStyles($styleAttributes, $pluck);
        }

        return $this->createStyleFromArray($styleAttributes);
    }

    /**
     * Set defaults into the styles array
     *
     * @param array $styleAttributes
     * @param array $defaults
     *
     * @return array
     */
    private function setDefaults(array $styleAttributes, array $defaults) : array
    {
        foreach ($defaults as $property => $default) {
            if (!isset($styleAttributes[$property])
                || (isset($styleAttributes[$property]) && empty($styleAttributes[$property]))
            ) {
                $styleAttributes[$property] = $default;
            }
        }

        return $styleAttributes;
    }

    /**
     * Pluck styles from the style array
     *
     * @param array $styles
     * @param array $pluck
     *
     * @return array
     */
    private function pluckStyles(array $styles, array $pluck) : array
    {
        return array_intersect_key($styles, array_fill_keys($pluck, null));
    }

    /**
     * Extract margins and paddings from form data
     *
     * @param array $formData
     * @param array $styleAttributes
     * @param array $defaults
     *
     * @return array
     */
    private function extractMarginPadding(array $formData, array $styleAttributes, array &$defaults) : array
    {
        if (isset($formData['metric']) && $formData['metric']) {
            $styleAttributes = $this->extractMetric($formData, 'margin', $styleAttributes);
            $styleAttributes = $this->extractMetric($formData, 'padding', $styleAttributes);

            // Remove any defaults for specific margins / paddings styles if we have the shorthand specified
            if (isset($styleAttributes['margin'])) {
                foreach ($this->marginStyles as $marginStyle) {
                    unset($defaults[$marginStyle]);
                }
            }
            if (isset($styleAttributes['padding'])) {
                foreach ($this->paddingStyles as $paddingStyle) {
                    unset($defaults[$paddingStyle]);
                }
            }
        }

        return $styleAttributes;
    }

    /**
     * Extract all advanced styles
     *
     * @param array $formData
     *
     * @return array
     */
    private function extractAdvancedStyles(array $formData) : array
    {
        return [
            'text-align' => isset($formData['align']) ? $formData['align'] : '',
            'width' => isset($formData['width']) ? $this->normalizeSizeDimension($formData['width']) : '',
            'height' => isset($formData['height']) ? $this->normalizeSizeDimension($formData['height']) : '',
            'background-color' => isset($formData['background_color']) && !empty($formData['background_color'])
                ? $this->colorConverter->convert($formData['background_color']) : '',
            'border-color' => isset($formData['border_color']) && !empty($formData['border_color'])
                ? $this->colorConverter->convert($formData['border_color']) : '',
            'border-width' => $formData['border_width'] ?? '',
            'display' => $formData['display'] ?? ''
        ];
    }

    /**
     * Create a string of styles from an array
     *
     * @param array $styles
     *
     * @return string
     */
    public function createStyleFromArray(array $styles) : string
    {
        $styleString = '';
        foreach ($styles as $attributeName => $attributeValue) {
            if ($attributeValue !== "") {
                $styleString .= "$attributeName: $attributeValue; ";
            }
        }

        return rtrim($styleString, ' ');
    }

    /**
     * Normalize value for width/height
     *
     * @param string $value
     * @return string
     */
    private function normalizeSizeDimension($value) : string
    {
        if (strpos($value, 'px') !== false || strpos($value, '%') !== false) {
            return $value;
        }
        return ($value * 100) . '%';
    }

    /**
     * Extract metric data from the form data
     *
     * @param array $formData
     * @param string $name padding|margin
     * @param array $styleAttributes
     *
     * @return array
     */
    private function extractMetric(array $formData, $name, array $styleAttributes) : array
    {
        $metric = $this->serializer->unserialize($formData['metric']);
        if (isset($metric[$name]) && $metric[$name] !== '- - - -') {
            $valuesArray = explode(' ', $metric[$name]);
            // Don't process invalid style rules
            if (count($valuesArray) > 4) {
                return $styleAttributes;
            }
            // Are all the values the same in the array?
            if (count(array_unique($valuesArray)) === 1) {
                $styleAttributes[$name] = $valuesArray[0];
            } else {
                $position = ['top', 'right', 'bottom', 'left'];
                foreach ($valuesArray as $index => $value) {
                    if ($value && $value !== "-" && isset($position[$index])) {
                        $styleAttributes[$name . "-" . $position[$index]] = intval($value) . 'px';
                    }
                }
            }
        }

        return $styleAttributes;
    }
}
