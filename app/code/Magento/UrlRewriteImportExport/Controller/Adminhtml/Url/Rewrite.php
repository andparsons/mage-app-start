<?php
declare(strict_types=1);

namespace Magento\UrlRewriteImportExport\Controller\Adminhtml\Url;

/**
 * The abstract controller to define ADMIN_RESOURCE
 */
abstract class Rewrite extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_UrlRewrite::urlrewrite';
}
