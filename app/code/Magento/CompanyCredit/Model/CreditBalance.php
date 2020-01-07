<?php

namespace Magento\CompanyCredit\Model;

/**
 * Credit balance increase, decrease and refund operations.
 */
class CreditBalance
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditLimitManagementInterface
     */
    private $creditLimitManagement;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Magento\CompanyCredit\Api\CreditBalanceManagementInterface
     */
    private $creditBalanceManagement;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyOrder
     */
    private $companyOrder;

    /**
     * @var \Magento\CompanyCredit\Model\CompanyStatus
     */
    private $companyStatus;

    /**
     * @var \Magento\CompanyCredit\Model\CreditBalanceOptionsFactory
     */
    private $creditBalanceOptionsFactory;

    /**
     * CreditBalanceManagement constructor.
     *
     * @param \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\CompanyCredit\Api\CreditBalanceManagementInterface $creditBalanceManagement
     * @param \Magento\CompanyCredit\Model\CompanyOrder $companyOrder
     * @param \Magento\CompanyCredit\Model\CompanyStatus $companyStatus
     * @param \Magento\CompanyCredit\Model\CreditBalanceOptionsFactory $creditBalanceOptionsFactory
     */
    public function __construct(
        \Magento\CompanyCredit\Api\CreditLimitManagementInterface $creditLimitManagement,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\CompanyCredit\Api\CreditBalanceManagementInterface $creditBalanceManagement,
        \Magento\CompanyCredit\Model\CompanyOrder $companyOrder,
        \Magento\CompanyCredit\Model\CompanyStatus $companyStatus,
        \Magento\CompanyCredit\Model\CreditBalanceOptionsFactory $creditBalanceOptionsFactory
    ) {
        $this->creditLimitManagement = $creditLimitManagement;
        $this->priceCurrency = $priceCurrency;
        $this->creditBalanceManagement = $creditBalanceManagement;
        $this->companyOrder = $companyOrder;
        $this->companyStatus = $companyStatus;
        $this->creditBalanceOptionsFactory = $creditBalanceOptionsFactory;
    }

    /**
     * Decrease company credit balance by order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $poNumber [optional]
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function decreaseBalanceByOrder(\Magento\Sales\Api\Data\OrderInterface $order, $poNumber = '')
    {
        $companyId = $this->companyOrder->getCompanyIdByOrder($order);
        $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($companyId);

        if (!$creditLimit->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The requested Payment Method is not available.')
            );
        } elseif (!$creditLimit->getExceedLimit() && $this->isLimitExceeded(
            (string)$creditLimit->getCurrencyCode(),
            (float)$creditLimit->getAvailableLimit(),
            $order
        )) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                'Payment On Account cannot be used for this order because your order amount exceeds your '
                . 'credit amount.'
            ));
        }

        $options = $this->creditBalanceOptionsFactory->create();
        $options->setData('purchase_order', $poNumber);
        $options->setData('order_increment', $order->getIncrementId());
        $options->setData('currency_display', $order->getOrderCurrencyCode());
        $options->setData('currency_base', $order->getBaseCurrencyCode());

        $this->creditBalanceManagement->decrease(
            $creditLimit->getId(),
            $order->getBaseGrandTotal(),
            $order->getBaseCurrencyCode(),
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_PURCHASED,
            '',
            $options
        );
    }

    /**
     * Increase company credit balance by order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function increaseBalanceByOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $companyId = $this->companyOrder->getCompanyIdByOrder($order);
        $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($companyId);

        $options = $this->creditBalanceOptionsFactory->create();
        $options->setData('order_increment', $order->getIncrementId());
        $options->setData('currency_display', $order->getOrderCurrencyCode());
        $options->setData('currency_base', $order->getBaseCurrencyCode());

        $this->creditBalanceManagement->increase(
            $creditLimit->getId(),
            $order->getBaseGrandTotal(),
            $order->getBaseCurrencyCode(),
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REVERTED,
            '',
            $options
        );
    }

    /**
     * Increase the company credit by amount of the refund.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function refund(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
    ) {
        $companyId = $this->companyOrder->getCompanyIdForRefund($order);
        $creditLimit = $this->creditLimitManagement->getCreditByCompanyId($companyId);
        $comments = $creditmemo->getComments();

        $options = $this->creditBalanceOptionsFactory->create();
        $options->setData('order_increment', $order->getIncrementId());
        $options->setData('currency_display', $order->getOrderCurrencyCode());
        $options->setData('currency_base', $order->getBaseCurrencyCode());

        $this->creditBalanceManagement->increase(
            $creditLimit->getId(),
            $this->priceCurrency->round($creditmemo->getBaseGrandTotal()),
            $creditmemo->getBaseCurrencyCode(),
            \Magento\CompanyCredit\Model\HistoryInterface::TYPE_REFUNDED,
            !empty($comments) ? $comments[0]->getComment() : '',
            $options
        );
    }

    /**
     * Revert credit to company when order is canceled.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cancel(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $companyId = $this->companyOrder->getCompanyIdByOrder($order);
        if ($companyId && $this->companyStatus->isRevertAvailable($companyId)) {
            $this->increaseBalanceByOrder($order);
            return true;
        }
        return false;
    }

    /**
     * Check if credit limit is exceeded.
     *
     * @param string $creditLimitCurrencyCode,
     * @param float $availableCreditLimit
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     * @throws \Exception
     */
    private function isLimitExceeded(
        $creditLimitCurrencyCode,
        $availableCreditLimit,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $value = $order->getBaseGrandTotal();
        if ($order->getOrderCurrencyCode() != $creditLimitCurrencyCode) {
            /** @var \Magento\Directory\Model\Currency $operationCurrency */
            $operationCurrency = $this->priceCurrency->getCurrency(true, $order->getBaseCurrencyCode());
            if ($operationCurrency->getRate($creditLimitCurrencyCode)) {
                $value = $operationCurrency->convert($value, $creditLimitCurrencyCode);
            }
        }
        return $availableCreditLimit < $value;
    }
}
