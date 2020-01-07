<?php

namespace Magento\Backend\Test\Block\System\Store;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Class FormPageActions
 * Form page actions block
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * Click on "Delete" button without acceptAlert
     *
     * @return void
     */
    public function delete()
    {
        $this->_rootElement->find($this->deleteButton)->click();
    }
}
