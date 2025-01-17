<?php
namespace Magento\Support\Model\Report\Group\Events;

use Magento\Framework\App\Area;

/**
 * All crontab events section
 */
class AllCrontabEventsSection extends AbstractEventsSection
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return (string)__('All Crontab Events');
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
        return Area::AREA_CRONTAB;
    }
}
