<?php

namespace Magento\NegotiableQuote\Block\Quote;

use Magento\NegotiableQuote\Api\Data\HistoryInterface;
use Magento\NegotiableQuote\Model\ResourceModel\History\Collection as HistoryCollection;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Block for preparing quote history data.
 *
 * @api
 * @since 100.0.0
 */
class History extends AbstractQuote
{
    /**
     * @var \Magento\NegotiableQuote\Model\History\LogInformation
     */
    private $historyLogInformation;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogProductInformation
     */
    private $historyLogProductInformation;

    /**
     * @var \Magento\NegotiableQuote\Model\History\LogCommentsInformation
     */
    private $historyLogCommentsInformation;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper
     * @param \Magento\NegotiableQuote\Model\History\LogInformation $historyLogInformation
     * @param \Magento\NegotiableQuote\Model\History\LogProductInformation $historyLogProductInformation
     * @param \Magento\NegotiableQuote\Model\History\LogCommentsInformation $historyLogCommentsInformation
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\NegotiableQuote\Helper\Quote $negotiableQuoteHelper,
        \Magento\NegotiableQuote\Model\History\LogInformation $historyLogInformation,
        \Magento\NegotiableQuote\Model\History\LogProductInformation $historyLogProductInformation,
        \Magento\NegotiableQuote\Model\History\LogCommentsInformation $historyLogCommentsInformation,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $negotiableQuoteHelper, $data);
        $this->historyLogInformation = $historyLogInformation;
        $this->historyLogProductInformation = $historyLogProductInformation;
        $this->historyLogCommentsInformation = $historyLogCommentsInformation;
    }

    /**
     * Get author name of the history log.
     *
     * @param HistoryInterface $historyLog
     * @return string
     */
    public function getLogAuthor(HistoryInterface $historyLog)
    {
        return $this->historyLogCommentsInformation->getLogAuthor($historyLog, $this->getQuote()->getId());
    }

    /**
     * Get history log for negotiable quote.
     *
     * @return HistoryCollection
     */
    public function getQuoteHistory()
    {
        return $this->historyLogInformation->getQuoteHistory();
    }

    /**
     * Prepare status message for the history log.
     *
     * @param HistoryInterface $historyLog
     * @return string
     */
    public function getLogStatusMessage(HistoryInterface $historyLog)
    {
        $statusMessage = '';
        $statusMessages = [
            HistoryInterface::STATUS_CREATED => __('created quote'),
            HistoryInterface::STATUS_CLOSED => __('closed quote'),
            HistoryInterface::STATUS_UPDATED => __('updated quote'),
            HistoryInterface::STATUS_UPDATED_BY_SYSTEM => __('updated quote')
        ];

        if ($historyLog->getStatus() && $statusMessages[$historyLog->getStatus()]) {
            $statusMessage = $statusMessages[$historyLog->getStatus()];
        }

        return $statusMessage;
    }

    /**
     * Prepare price type label.
     *
     * @param array $price
     * @return string
     */
    public function getPriceValue(array $price)
    {
        $priceType = current(array_keys($price));
        $priceValue = current($price);
        $priceTypeLabel = '';
        $priceValueLabels = [
            NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PERCENTAGE_DISCOUNT =>
                __('Percentage Discount - %1%', $priceValue),
            NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_AMOUNT_DISCOUNT =>
                __('Amount Discount - %1', $this->negotiableQuoteHelper->formatPrice($priceValue)),
            NegotiableQuoteInterface::NEGOTIATED_PRICE_TYPE_PROPOSED_TOTAL =>
                __('Proposed Price - %1', $this->negotiableQuoteHelper->formatPrice($priceValue))
        ];

        if ($priceType && !empty($priceValueLabels[$priceType])) {
            $priceTypeLabel = $priceValueLabels[$priceType];
        }

        return $priceTypeLabel;
    }

    /**
     * Prepare removed prices data.
     *
     * @param array $price
     * @return array
     */
    public function getRemovedPriceValues(array $price)
    {
        $removedPrices = [];
        $priceString = $this->getPriceValue($price);
        $priceData = explode(' - ', $priceString);

        if (count($priceData) > 1) {
            list($removedPrices['method'], $removedPrices['value']) = $priceData;
        }

        return $removedPrices;
    }

    /**
     * Return object with quote updates.
     *
     * @param int $logId
     * @return \Magento\Framework\DataObject
     */
    public function getUpdates($logId)
    {
        return $this->historyLogInformation->getQuoteUpdates($logId);
    }

    /**
     * Prepare history log comment text.
     *
     * @param int $commentId
     * @return string
     */
    public function getCommentText($commentId)
    {
        return $this->historyLogCommentsInformation->getCommentText($commentId);
    }

    /**
     * Get list of the comment attachments.
     *
     * @param int $commentId
     * @return \Magento\NegotiableQuote\Model\ResourceModel\CommentAttachment\Collection
     */
    public function getCommentAttachments($commentId)
    {
        return $this->historyLogCommentsInformation->getCommentAttachments($commentId);
    }

    /**
     * Get history log attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getAttachmentUrl($attachmentId)
    {
        return $this->getUrl('negotiable_quote/quote/download', ['attachmentId' => $attachmentId]);
    }

    /**
     * Prepare history log status label.
     *
     * @param string $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        return $this->historyLogCommentsInformation->getStatusLabel($status);
    }

    /**
     * Prepare formatted price.
     *
     * @param string $priceValue
     * @return string
     */
    public function formatPrice($priceValue)
    {
        return $this->negotiableQuoteHelper
            ->formatPrice($priceValue, $this->getQuote()->getCurrency()->getBaseCurrencyCode());
    }

    /**
     * Get product name by sku.
     *
     * @param string $sku
     * @return string
     */
    public function getProductName($sku)
    {
        return $this->historyLogProductInformation->getProductName($sku);
    }

    /**
     * Get product name by ID.
     *
     * @param int $id
     * @return string
     */
    public function getProductNameById($id)
    {
        return $this->historyLogProductInformation->getProductNameById($id);
    }

    /**
     * Get customer address html.
     *
     * @param array $flatAddressArray
     * @return string
     */
    public function getAddressHtml(array $flatAddressArray)
    {
        $addressHtml = __('None');

        if ($this->historyLogInformation->isSetPostcode($flatAddressArray)) {
            $renderer = $this->historyLogInformation->getLogAddressRenderer();
            $addressHtml = $renderer->renderArray($flatAddressArray);
        }

        return $addressHtml;
    }

    /**
     * Prepare shipping method name.
     *
     * @param array $data
     * @return string
     */
    public function getShippingMethodName(array $data)
    {
        if (isset($data['method']) && $data['method'] != '') {
            return $data['method'];
        }

        return __('None');
    }

    /**
     * Prepare formatted date.
     *
     * @param string $date
     * @param int $dateType [optional]
     * @return string
     */
    public function formatExpirationDate($date, $dateType = \IntlDateFormatter::LONG)
    {
        return $this->historyLogInformation->formatDate($date, $dateType);
    }

    /**
     * Check for multi-line status update.
     *
     * @param string $oldValue
     * @param string $newValue
     * @return array
     */
    public function checkMultiStatus($oldValue, $newValue)
    {
        $multiStatuses = [];
        if (($oldValue == NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN
                || $oldValue == NegotiableQuoteInterface::STATUS_DECLINED)
            && $newValue == NegotiableQuoteInterface::STATUS_ORDERED
        ) {
            $multiStatuses[] = [
                'old_value' => $oldValue,
                'new_value' => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
            ];
            $multiStatuses[] = [
                'old_value' => NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
                'new_value' => $newValue,
            ];
        }
        return $multiStatuses;
    }

    /**
     * Is quote can be submitted.
     *
     * @return bool
     */
    public function isCanSubmit()
    {
        return $this->historyLogInformation->isCanSubmit();
    }

    /**
     * Generate added product attribute values as a string.
     *
     * @param array $productData
     * @return string
     */
    public function getProductAddStringHtml(array $productData)
    {
        $updateStrings = [];
        foreach ($productData as $propertyName => $values) {
            if ($propertyName == 'product_id') {
                continue;
            }
            if ($propertyName == 'qty') {
                $updateStrings[$propertyName] = $this->escapeHtml(__('Qty:')) . ' ' . $values;
            }
            if ($propertyName == 'options') {
                if (isset($productData['product_id'])) {
                    $optionsStrings = $this->getAddedOptionsHtml($productData['product_id'], $values);
                    $updateStrings = array_merge($updateStrings, $optionsStrings);
                }
            }
        }

        return implode(', ', $updateStrings);
    }

    /**
     * Get result HTML string for product updates.
     *
     * @param array $productUpdates
     * @return string
     */
    public function getProductUpdateStringHtml(array $productUpdates)
    {
        $updateStrings = [];
        foreach ($productUpdates as $updateName => $values) {
            if ($updateName == 'product_id') {
                continue;
            }
            if ($updateName == 'qty_changed') {
                $updateStrings[$updateName] = $this->escapeHtml(__('Qty: '));
                $updateStrings[$updateName] .= $this->getDiffValuesHtml($values['old_value'], $values['new_value']);
            }
            if ($updateName == 'options_changed' && is_array($values)) {
                if (isset($productUpdates['product_id'])) {
                    $optionsUpdateStrings = $this->getOptionsDiffHtml(
                        $productUpdates['product_id'],
                        $values
                    );
                    $updateStrings = array_merge($updateStrings, $optionsUpdateStrings);
                }
            }
        }

        return implode(', ', $updateStrings);
    }

    /**
     * Prepare deleted history log sku message.
     *
     * @param string $sku
     * @return string
     */
    public function getDeletedSkuMessage($sku)
    {
        return '<b>' . $this->escapeHtml($sku) . '</b>' . __(' - deleted from catalog');
    }

    /**
     * Get message for the removed product.
     *
     * @param array $product
     * @return string
     */
    public function getRemovedProductMessage(array $product)
    {
        $message = '';

        if (isset($product['product_id'])) {
             $message = '<b>'
             . $this->escapeHtml($this->getProductNameById($product['product_id']))
             . '</b>'
             . __('(Removed)');
        }

        return $message;
    }

    /**
     * Remove negotiated_price from data.
     *
     * @param array $data
     * @return array
     */
    protected function processData(array $data)
    {
        if (isset($data['negotiated_price'])) {
            unset($data['negotiated_price']);
        }
        return $data;
    }

    /**
     *  Get HTML for options diff string.
     *
     * @param int $productId
     * @param array $values
     * @return string
     */
    private function getOptionsDiffHtml($productId, array $values)
    {
        $resultStringsArray = [];
        $productAttributesArray = $this->historyLogProductInformation->getProductAttributes($productId);

        foreach ($values as $optionId => $attributeUpdates) {
            $resultStringsArray[$optionId] = $this->escapeHtml(
                $this->getAttributeLabelFromArray($productAttributesArray, $optionId) . ': '
            );
            $resultStringsArray[$optionId] .= $this->getDiffValuesHtml(
                $this->getAttributeValueLabelFromArray(
                    $productAttributesArray,
                    $optionId,
                    (array)$attributeUpdates['old_value']
                ),
                $this->getAttributeValueLabelFromArray(
                    $productAttributesArray,
                    $optionId,
                    (array)$attributeUpdates['new_value']
                )
            );
        }

        return $resultStringsArray;
    }

    /**
     * Generate array of configurable attributes for added product.
     *
     * @param int $productId
     * @param array $values
     * @return array
     */
    private function getAddedOptionsHtml($productId, array $values)
    {
        $resultStringsArray = [];
        $productAttributesArray = $this->historyLogProductInformation->getProductAttributes($productId);
        foreach ($values as $option) {
            if (isset($option['option']) && isset($option['value'])) {
                $resultStringsArray[$option['option']] = $this->escapeHtml(
                    $this->getAttributeLabelFromArray($productAttributesArray, $option['option']) . ': '
                );
                $resultStringsArray[$option['option']] .= $this->escapeHtml(
                    $this->getAttributeValueLabelFromArray(
                        $productAttributesArray,
                        $option['option'],
                        is_array($option['value']) ? $option['value'] : [$option['value']]
                    )
                );
            }
        }
        return $resultStringsArray;
    }

    /**
     * Get HTML for product update values.
     *
     * @param string $oldValue
     * @param string $newValue
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDiffValuesHtml($oldValue, $newValue)
    {
        $html = '';
        $data = [
            'old_value' => $oldValue,
            'new_value' => $newValue
        ];
        /** @var $block \Magento\Framework\View\Element\AbstractBlock */
        $block = $this->getLayout()->getBlock('diff.values');
        if ($block) {
            $html .= $block->setData(
                $data
            )->toHtml();
        }

        return $html;
    }

    /**
     *  Retrieve attribute label from array.
     *
     * @param array $arrayOfProductAttributes
     * @param int $attributeId
     * @return string
     */
    private function getAttributeLabelFromArray(array $arrayOfProductAttributes, $attributeId)
    {
        if (isset($arrayOfProductAttributes[$attributeId])
            && isset($arrayOfProductAttributes[$attributeId]['label'])) {
            return $arrayOfProductAttributes[$attributeId]['label'];
        } else {
            return __('deleted');
        }
    }

    /**
     * Retrieve attribute option label from array.
     *
     * @param array $arrayOfProductAttributes
     * @param int $attributeId
     * @param array $values
     * @return string
     */
    private function getAttributeValueLabelFromArray(array $arrayOfProductAttributes, $attributeId, array $values)
    {
        $label = __('deleted');
        if (isset($arrayOfProductAttributes[$attributeId])
            && isset($arrayOfProductAttributes[$attributeId]['values'])
            && is_array($arrayOfProductAttributes[$attributeId]['values'])) {
            $labels = [];
            foreach ($arrayOfProductAttributes[$attributeId]['values'] as $option) {
                if (in_array($option['value_index'], $values)) {
                    $labels[] = $option['label'];
                }
            }
            $label = implode(', ', empty($labels) ? $values : $labels);
        }
        return $label;
    }
}
