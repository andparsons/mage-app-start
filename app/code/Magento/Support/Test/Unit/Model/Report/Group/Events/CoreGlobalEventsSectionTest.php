<?php
namespace Magento\Support\Test\Unit\Model\Report\Group\Events;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ConfigInterface;

class CoreGlobalEventsSectionTest extends AbstractEventsSectionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getExpectedType()
    {
        return ConfigInterface::TYPE_CORE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSectionName()
    {
        return \Magento\Support\Model\Report\Group\Events\CoreGlobalEventsSection::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedTitle()
    {
        return (string)__('Core Global Events');
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAreaCode()
    {
        return Area::AREA_GLOBAL;
    }
}
