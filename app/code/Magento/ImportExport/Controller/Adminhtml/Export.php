<?php
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Export controller
 */
abstract class Export extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_ImportExport::export';
}
