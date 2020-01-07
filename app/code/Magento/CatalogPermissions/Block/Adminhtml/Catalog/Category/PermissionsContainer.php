<?php

namespace Magento\CatalogPermissions\Block\Adminhtml\Catalog\Category;

class PermissionsContainer extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\CatalogPermissions\Block\Adminhtml\Catalog\Category\Tab\Permissions::class,
            'category.permissions.row'
        )->toHtml();
    }
}
