<?php
namespace Magento\Support\Model\Report\Group\Events;

use Magento\Framework\App\Area;

/**
 * All admin events section
 */
class AllAdminEventsSection extends AbstractEventsSection
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return (string)__('All Admin Events');
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
        return Area::AREA_ADMINHTML;
    }
}
