<?php

/**
 * Newsletter queue controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Controller\Adminhtml;

abstract class Queue extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Newsletter::queue';
}
