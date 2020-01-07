<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Company\Plugin\Wishlist\Block\Customer;

use Magento\Wishlist\Block\Customer\Sidebar;

/**
 * Plugin for Wishlist Sidebar class.
 */
class SidebarPlugin
{
    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var \Magento\Company\Api\CompanyManagementInterface
     */
    private $companyManagement;

    /**
     * SidebarPlugin constructor.
     *
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     */
    public function __construct(
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement
    ) {
        $this->companyContext = $companyContext;
        $this->companyManagement = $companyManagement;
    }

    /**
     * Around to html.
     *
     * @param Sidebar $subject
     * @param \Closure $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundToHtml(Sidebar $subject, \Closure $proceed)
    {
        return $this->companyContext->getCustomerId() &&
               $this->companyManagement->getByCustomerId($this->companyContext->getCustomerId()) ? '' : $proceed();
    }
}
