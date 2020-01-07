<?php
namespace Magento\Backend\Model\View\Page;

use Magento\Backend\Model\View\Layout;
use Magento\Framework\App;
use Magento\Framework\Event;
use Magento\Framework\View;

/**
 * @api
 * @since 100.0.2
 */
class Builder extends View\Page\Builder
{
    /**
     * @return $this
     */
    protected function afterGenerateBlock()
    {
        $this->layout->initMessages();
        return $this;
    }
}
