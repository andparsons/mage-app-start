<?php
namespace Magento\Support\Model\Report\Group\Events;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ConfigInterface;

/**
 * Custom global events section
 */
class CustomGlobalEventsSection extends AbstractEventsSection
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return (string)__('Custom Global Events');
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return ConfigInterface::TYPE_CUSTOM;
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCode()
    {
        return Area::AREA_GLOBAL;
    }
}
