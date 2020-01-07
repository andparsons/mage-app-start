<?php

namespace Magento\NegotiableQuote\Model\Status;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;

/**
 * Class FrontendLabelProvider
 */
class FrontendLabelProvider extends AbstractLabelProvider
{
    /**
     * {@inheritdoc}
     */
    public function getStatusLabels()
    {
        return [
            NegotiableQuoteInterface::STATUS_CREATED => __('Submitted'),
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER => __('Open'),
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN => __('Pending'),
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER => __('Submitted'),
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN => __('Updated'),
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
                'Catalog prices have changed. You may want to re-submit this quote. '
                . 'Details about the changes are available in the History Log.'
            ),
            NegotiableQuoteInterface::DISCOUNT_CHANGED => __(
                'The discount has changed for this quote. Check the History Log for details.'
            ),
            NegotiableQuoteInterface::DISCOUNT_LIMIT => __(
                'The discount for this quote has been removed because you have already used your discount '
                . 'on previous orders. Please re-submit the quote to the Seller for further negotiation.'
            ),
            NegotiableQuoteInterface::DISCOUNT_REMOVED => __(
                'The discount is no longer valid for this quote, and the prices have been updated. '
                . 'Please re-submit the quote to the Seller for further negotiation.'
            ),
            NegotiableQuoteInterface::TAX_CHANGED => __(
                'Quote taxes have been recalculated because you have added or changed the shipping address. '
                . 'The negotiated price is no longer valid. Please re-submit the quote to Seller.'
            ),
            NegotiableQuoteInterface::ADDRESS_CHANGED => __(
                'You have added or changed the shipping address on this quote. Please re-submit the quote to Seller.'
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
                'one' => __('is no longer available in catalog. It was removed from your quote.'),
                'many' => __('are no longer available in catalog. They were removed from your quote.')
            ],
            'unlocked' => [
                'one' => [
                    'negotiable' => __('is no longer available. It was removed from your quote.'),
                    'nonnegotiable' => __('is no longer available. It was removed from your quote.')
                ],
                'many' => [
                    'negotiable' => __('are no longer available. They were removed from your quote.'),
                    'nonnegotiable' => __('are no longer available. They were removed from your quote.')
                ]
            ]
        ];
    }
}
