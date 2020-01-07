<?php
declare(strict_types=1);

namespace Magento\CompanyCredit\Block\Adminhtml\Order;

use Magento\CompanyCredit\Model\CompanyCreditPaymentConfigProvider;

/**
 * Changes confirmation message for Cancel button if company is not active.
 *
 * @api
 * @since 100.0.0
 */
class CancelButton extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus
     */
    private $companyStatus;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyOrder
     */
    private $companyOrder;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Helper\Reorder $reorderHelper
     * @param \Magento\CompanyCredit\Model\CompanyStatus $companyStatus
     * @param \Magento\CompanyCredit\Model\CompanyOrder $companyOrder
     * @param \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        \Magento\CompanyCredit\Model\CompanyStatus $companyStatus,
        \Magento\CompanyCredit\Model\CompanyOrder $companyOrder,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        array $data = []
    ) {
        $this->companyStatus = $companyStatus;
        $this->companyOrder = $companyOrder;
        $this->companyRepository = $companyRepository;
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    /**
     * Create block for cancel button
     *
     * @return \Magento\Sales\Block\Adminhtml\Order\View
     * @since 100.1.3
     */
    protected function _prepareLayout()
    {
        $this->checkCompanyStatus();
        return parent::_prepareLayout();
    }

    /**
     * Replace confirmation message for Cancel button if company is not active or doesn't exist.
     *
     * @return $this
     */
    public function checkCompanyStatus()
    {
        $order = $this->getOrder();
        if ($order && $order->getId()
            && $order->getPayment()->getMethod() == CompanyCreditPaymentConfigProvider::METHOD_NAME
        ) {
            $confirm = null;
            $companyId = $this->companyOrder->getCompanyIdByOrder($order);
            if ($companyId && !$this->companyStatus->isRevertAvailable($companyId)) {
                try {
                    $company = $this->companyRepository->get($companyId);
                    $confirm = __(
                        'Are you sure you want to cancel this order? '
                        . 'The order amount will not be reverted to %1 because the company is not active.',
                        $company->getCompanyName()
                    );
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $companyId = null;
                }
            }

            if (!$companyId) {
                $companyName = $order->getPayment()->getAdditionalInformation('company_name');
                $confirm = __(
                    'Are you sure you want to cancel this order? The order amount will not be reverted '
                    . 'to %1 because the company associated with this customer does not exist.',
                    $companyName
                );
            }

            if ($confirm) {
                $this->updateButton(
                    'order_cancel',
                    'data_attribute',
                    [
                        'mage-init' => '{"Magento_CompanyCredit/js/cancel-order-button": '
                            . '{"message": "' . $confirm . '", "url": "' . $this->getCancelUrl() . '"}}',
                    ]
                );
            }
        }
        return $this;
    }
}
