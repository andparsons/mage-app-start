<?php
namespace Magento\Customer\Controller\Adminhtml\Index;

class Orders extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer orders grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
