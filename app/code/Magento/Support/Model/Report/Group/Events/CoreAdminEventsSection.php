<?php
namespace Magento\Support\Model\Report\Group\Events;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ConfigInterface;

/**
 * Core admin events section
 */
class CoreAdminEventsSection extends AbstractEventsSection
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return (string)__('Core Admin Events');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return ConfigInterface::TYPE_CORE;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCode()
    {
        return Area::AREA_ADMINHTML;
    }
}
