<?php
namespace Magento\Support\Test\Unit\Model\Report\Group\Events;

use Magento\Framework\App\Area;
use Magento\Framework\Event\ConfigInterface;

class CoreRestEventsSectionTest extends AbstractEventsSectionTest
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
    protected function getExpectedTitle()
    {
        return (string)__('Core REST Events');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSectionName()
    {
        return \Magento\Support\Model\Report\Group\Events\CoreRestEventsSection::class;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAreaCode()
    {
        return Area::AREA_WEBAPI_REST;
    }
}
