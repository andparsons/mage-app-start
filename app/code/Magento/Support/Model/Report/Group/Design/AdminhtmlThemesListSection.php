<?php
namespace Magento\Support\Model\Report\Group\Design;

/**
 * Adminhtml Themes List report
 */
class AdminhtmlThemesListSection extends AbstractDesignSection
{
    /**
     * Admin Area
     */
    const AREA = 'adminhtml';

    /**
     * Generate Themes list information
     *
     * @return array
     */
    public function generate()
    {
        return $this->generateReport(__('Adminhtml Themes List'));
    }
}
