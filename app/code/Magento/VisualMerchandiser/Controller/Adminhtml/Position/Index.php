<?php
namespace Magento\VisualMerchandiser\Controller\Adminhtml\Position;

use Magento\Framework\Exception\NotFoundException;

class Index extends \Magento\VisualMerchandiser\Controller\Adminhtml\Position
{
    /**
     * Index action
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        throw new NotFoundException(__('Page not found.'));
    }
}
