<?php

/**
 * Locked administrators controller
 */
namespace Magento\User\Controller\Adminhtml;

abstract class Locks extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_User::locks';
}
