<?php

namespace Magento\NegotiableQuote\Model\Status;

/**
 * Interface LabelProviderInterface
 */
interface LabelProviderInterface
{
    /**
     * Retrieve status labels array
     *
     * @return array
     */
    public function getStatusLabels();

    /**
     * Get label value by status
     *
     * @param string $status
     * @return string
     */
    public function getLabelByStatus($status);

    /**
     * Retrieve status labels array
     *
     * @return array
     */
    public function getMessageLabels();

    /**
     * Retrieve removed sku labels array
     *
     * @return array
     */
    public function getRemovedSkuMessageLabels();

    /**
     * Get label value by status
     *
     * @param string $code
     * @return string
     */
    public function getMessageByCode($code);

    /**
     * Get removed SKU message
     *
     * @param array $sku
     * @param bool $isNegotiable
     * @param bool $isLocked
     * @return string
     */
    public function getRemovedSkuMessage(array $sku, $isNegotiable = false, $isLocked = false);
}
