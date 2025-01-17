<?php

namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Main controller of the Setup Wizard
 */
class Index extends AbstractActionController
{
    /**
     * @return ViewModel|\Zend\Http\Response
     */
    public function indexAction()
    {
        return new ViewModel();
    }
}
