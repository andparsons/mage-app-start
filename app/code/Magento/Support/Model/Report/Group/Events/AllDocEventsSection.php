<?php
namespace Magento\Support\Model\Report\Group\Events;

use Magento\Framework\App\Area;

/**
 * All doc events section
 */
class AllDocEventsSection extends AbstractEventsSection
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return (string)__('All Doc Events');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCode()
    {
        return Area::AREA_DOC;
    }
}
