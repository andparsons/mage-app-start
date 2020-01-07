<?php
namespace Magento\Customer\Controller\Adminhtml\Index;

class ViewWishlist extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Customer last view wishlist for ajax
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
