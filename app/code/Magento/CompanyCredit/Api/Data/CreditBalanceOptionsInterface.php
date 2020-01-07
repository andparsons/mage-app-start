<?php

namespace Magento\CompanyCredit\Api\Data;

/**
 * Credit balance data transfer object interface.
 *
 * @api
 * @since 100.0.0
 */
interface CreditBalanceOptionsInterface
{
    const PURCHASE_ORDER = 'purchase_order';
    const ORDER_INCREMENT = 'order_increment';
    const CURRENCY_DISPLAY = 'currency_display';
    const CURRENCY_BASE = 'currency_base';

    /**
     * Get purchase order number.
     *
     * @return string
     */
    public function getPurchaseOrder();

    /**
     * Set purchase order number.
     *
     * @param string $purchaseOrder
     * @return $this
     */
    public function setPurchaseOrder($purchaseOrder);

    /**
     * Get order increment.
     *
     * @return string
     */
    public function getOrderIncrement();

    /**
     * Set order increment.
     *
     * @param string $orderIncrement
     * @return $this
     */
    public function setOrderIncrement($orderIncrement);

    /**
     * Get currency display.
     *
     * @return string
     */
    public function getCurrencyDisplay();

    /**
     * Set the currency display from the order.
     *
     * @param bool $currencyDisplay
     * @return $this
     */
    public function setCurrencyDisplay($currencyDisplay);

    /**
     * Get currency base.
     *
     * @return string
     */
    public function getCurrencyBase();

    /**
     * Set currency base from the order.
     *
     * @param bool $currencyBase
     * @return $this
     */
    public function setCurrencyBase($currencyBase);
}
