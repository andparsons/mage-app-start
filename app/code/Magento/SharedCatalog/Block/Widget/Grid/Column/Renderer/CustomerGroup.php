<?php
namespace Magento\SharedCatalog\Block\Widget\Grid\Column\Renderer;

/**
 * Grid column widget for render customer group options.
 */
class CustomerGroup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @inheritdoc
     */
    protected function _getOptions()
    {
        if (empty($this->options)) {
            $this->options = $this->transformToFlatArray($this->getColumn()->getOptions());
        }

        return $this->options;
    }

    /**
     * Transform multidimensional options array to flat array.
     *
     * @param array $options
     * @return array
     */
    private function transformToFlatArray(array $options)
    {
        $output = [];
        foreach ($options as $option) {
            if (is_array($option['value'])) {
                $output += $this->transformToFlatArray($option['value']);
            } else {
                $output[$option['value']] = $option;
            }
        }

        return $output;
    }
}
