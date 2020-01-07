<?php
namespace Magento\Company\Controller\Role;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class Index.
 */
class Index extends \Magento\Company\Controller\AbstractAction implements HttpGetActionInterface
{
    /**
     * Authorization level of a company session.
     */
    const COMPANY_RESOURCE = 'Magento_Company::roles_view';

    /**
     * Roles and permissions grid.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->loadLayoutUpdates();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Roles and Permissions'));
        $this->_view->renderLayout();
    }
}
