<?php

namespace Magento\NegotiableQuote\Model\Restriction;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface as NegotiableQuote;

/**
 * Class defines actions that allowed for admin depending on quote status.
 */
class Admin extends AbstractRestriction
{
    /**
     * {@inheritdoc}
     */
    protected $allowedActionsByStatus = [
        NegotiableQuote::STATUS_CREATED => [
            self::ACTION_SUBMIT
        ],
        NegotiableQuote::STATUS_PROCESSING_BY_ADMIN => [
            self::ACTION_SUBMIT,
            self::ACTION_DECLINE
        ],
        NegotiableQuote::STATUS_SUBMITTED_BY_CUSTOMER => [
            self::ACTION_SUBMIT
        ],
    ];
}
