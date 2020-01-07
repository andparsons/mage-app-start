<?php

namespace Magento\Company\Block\Link;

/**
 * Class CompanyLink.
 *
 * @api
 * @since 100.0.0
 */
class CompanyLink extends Current implements \Magento\Customer\Block\Account\SortLinkInterface
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
     * CompanyLink constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\Company\Api\CompanyManagementInterface $companyManagement
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\Company\Api\CompanyManagementInterface $companyManagement,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $companyContext, $data);
        $this->companyContext = $companyContext;
        $this->companyManagement = $companyManagement;
    }

    /**
     * @return bool
     */
    protected function isVisible()
    {
        $company = null;
        $isRegistrationAllowed = $this->companyContext->isStorefrontRegistrationAllowed();
        if ($this->companyContext->getCustomerId()) {
            $company = $this->companyManagement->getByCustomerId($this->companyContext->getCustomerId());
        }
        return !$company && $isRegistrationAllowed || parent::isVisible();
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
