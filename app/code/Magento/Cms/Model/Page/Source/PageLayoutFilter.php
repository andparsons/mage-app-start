<?php
namespace Magento\Cms\Model\Page\Source;

/**
 * Page layout filter source
 */
class PageLayoutFilter extends PageLayout
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array_merge([['label' => '', 'value' => '']], parent::toOptionArray());
    }
}
