<?php

namespace Magento\CompanyCredit\Block\Link;

/**
 * Delimiter for account navigation.
 *
 * @api
 * @since 100.0.0
 */
class Delimiter extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Company\Model\CompanyContext
     */
    private $companyContext;

    /**
     * @var array
     */
    private $resources;

    /**
     * @var \Magento\CompanyCredit\Model\PaymentMethodStatus
     */
    private $paymentMethodStatus;

    /**
     * CompanyCreditLink constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Company\Model\CompanyContext $companyContext
     * @param \Magento\CompanyCredit\Model\PaymentMethodStatus $paymentMethodStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Company\Model\CompanyContext $companyContext,
        \Magento\CompanyCredit\Model\PaymentMethodStatus $paymentMethodStatus,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->companyContext = $companyContext;
        $this->paymentMethodStatus = $paymentMethodStatus;
        $this->resources = isset($data['resources']) && is_array($data['resources'])
            ? array_values($data['resources'])
            : [];
    }

    /**
     * Return HTML only if block visible.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isVisible()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Determine if the block is visible.
     *
     * @return bool
     */
    private function isVisible()
    {
        $isVisible = false;
        $isActive = $this->companyContext->isModuleActive();
        if ($isActive) {
            $isVisible = $this->isResourceAllowed() && $this->paymentMethodStatus->isEnabled();
        }

        return $isVisible;
    }

    /**
     * Determine if any of assigned resources is allowed.
     *
     * @return bool
     */
    private function isResourceAllowed()
    {
        $result = false;
        foreach ($this->resources as $resource) {
            if ($this->companyContext->isResourceAllowed($resource) === true) {
                $result = true;
                break;
            }
        }

        return $result;
    }
}
