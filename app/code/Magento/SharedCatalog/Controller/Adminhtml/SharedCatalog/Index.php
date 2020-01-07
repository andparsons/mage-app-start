<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Controller\Adminhtml\SharedCatalog;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends AbstractAction implements HttpGetActionInterface
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        return $this->createResultPage();
    }
}
