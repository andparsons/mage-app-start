<?php

namespace Magento\CompanyCredit\Block\Customer;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class Link.
 *
 * @api
 * @since 100.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link\Current implements SortLinkInterface
{
    /**
     * @var \Magento\Company\Api\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var \Magento\CompanyCredit\Model\PaymentMethodStatus
     */
    private $paymentMethodStatus;

    /**
     * Link constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Company\Api\AuthorizationInterface $authorization
     * @param \Magento\CompanyCredit\Model\PaymentMethodStatus $paymentMethodStatus
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Company\Api\AuthorizationInterface $authorization,
        \Magento\CompanyCredit\Model\PaymentMethodStatus $paymentMethodStatus,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->authorization = $authorization;
        $this->paymentMethodStatus = $paymentMethodStatus;
        if (isset($data['resource'])) {
            $this->resource = $data['resource'];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        if ($this->authorization->isAllowed($this->resource) && $this->paymentMethodStatus->isEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
