<?php

namespace Magento\VisualMerchandiser\Block\Adminhtml\Widget\Select;

use \Magento\VisualMerchandiser\Block\Adminhtml\Widget\Select;
use \Magento\VisualMerchandiser\Model\Sorting;

/**
 * @api
 * @since 100.0.2
 */
class SortOrderSelect extends Select
{
    /**
     * Get Select option values
     *
     * @return array
     */
    public function getSelectOptions()
    {
        return $this->_sorting->getSortingOptions();
    }
}
