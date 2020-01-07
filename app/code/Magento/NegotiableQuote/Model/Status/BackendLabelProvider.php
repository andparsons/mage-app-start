<?php

namespace Magento\NegotiableQuote\Model\Status;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class BackendLabelProvider
 */
class BackendLabelProvider extends AbstractLabelProvider
{
    /**
     * {@inheritdoc}
     */
    public function getStatusLabels()
    {
        return [
            NegotiableQuoteInterface::STATUS_CREATED => __('New'),
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER => __('Client reviewed'),
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN => __('Open'),
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER => __('Updated'),
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN => __('Submitted'),
            NegotiableQuoteInterface::STATUS_ORDERED => __('Ordered'),
            NegotiableQuoteInterface::STATUS_EXPIRED => __('Expired'),
            NegotiableQuoteInterface::STATUS_DECLINED => __('Declined'),
            NegotiableQuoteInterface::STATUS_CLOSED => __('Closed'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageLabels()
    {
        return [
            NegotiableQuoteInterface::ITEMS_CHANGED => __(
                'Catalog prices have changed, and you may want to review the offer. '
                . 'Details about the changes are available in the History Log.'
            ),
            NegotiableQuoteInterface::DISCOUNT_CHANGED => __(
                'The discount has changed for this quote. Check the History Log for details.'
            ),
            NegotiableQuoteInterface::DISCOUNT_LIMIT => __(
                'The discount for this quote has been removed because the buyer has reached the maximum number '
                . 'of attempts to request a discount. You may want to review the offer amount.'
            ),
            NegotiableQuoteInterface::DISCOUNT_REMOVED => __(
                'The discount for this quote has been removed because the cart rule has been deleted. '
                . 'You may want to review the offer amount.'
            ),
            NegotiableQuoteInterface::TAX_CHANGED => __(
                'Quote taxes have been recalculated because the buyer added or changed the shipping address. '
                . 'You may want to review the offer amount.'
            ),
            NegotiableQuoteInterface::ADDRESS_CHANGED => __(
                'Buyer added or changed the shipping address. '
                . 'You may want to offer the shipping price or / and review the offer amount.'
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedSkuMessageLabels()
    {
        return [
            'locked' => [
                'one' => __('has been deleted from the catalog, so the items quoted list has been updated.'),
                'many' => __(
                    'have been deleted from the catalog, so the items quoted list has been updated.'
                )
            ],
            'unlocked' => [
                'one' => [
                    'negotiable' => __(
                        'has been deleted from the quote. '
                        . 'You may want to review the offer amount.'
                    ),
                    'nonnegotiable' => __(
                        'has been deleted from the catalog. '
                        . 'The items quoted list has been updated.'
                    )
                ],
                'many' => [
                    'negotiable' => __(
                        'have been deleted from the quote. '
                        . 'You may want to review the offer amount.'
                    ),
                    'nonnegotiable' => __(
                        'have been deleted from the catalog. '
                        . 'The items quoted list has been updated.'
                    )
                ]
            ]
        ];
    }
}
