<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResource;

/**
 * Validator for changing negotiable quote status.
 */
class NegotiableStatusChange implements ValidatorInterface
{
    /**
     * @var NegotiableQuoteInterfaceFactory
     */
    private $negotiableQuoteFactory;

    /**
     * @var NegotiableQuoteResource
     */
    private $negotiableQuoteResource;

    /**
     * @var \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory
     */
    private $validatorResultFactory;

    /**
     * @var array
     */
    private $allowChanges = [
        '' => [NegotiableQuoteInterface::STATUS_CREATED],
        NegotiableQuoteInterface::STATUS_CREATED => [
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN,
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
            NegotiableQuoteInterface::STATUS_DECLINED,
            NegotiableQuoteInterface::STATUS_CLOSED,
            NegotiableQuoteInterface::STATUS_EXPIRED,
        ],
        NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER => [
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN,
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
            NegotiableQuoteInterface::STATUS_DECLINED,
            NegotiableQuoteInterface::STATUS_CLOSED,
        ],
        NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN => [
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_ORDERED,
            NegotiableQuoteInterface::STATUS_CLOSED,
            NegotiableQuoteInterface::STATUS_EXPIRED,
        ],
        NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER => [
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_CLOSED,
            NegotiableQuoteInterface::STATUS_EXPIRED,
        ],
        NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN => [
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_ADMIN,
            NegotiableQuoteInterface::STATUS_DECLINED,
            NegotiableQuoteInterface::STATUS_CLOSED,
        ],
        NegotiableQuoteInterface::STATUS_ORDERED => [],
        NegotiableQuoteInterface::STATUS_EXPIRED => [
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_ORDERED,
            NegotiableQuoteInterface::STATUS_CLOSED,
        ],
        NegotiableQuoteInterface::STATUS_DECLINED => [
            NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_PROCESSING_BY_CUSTOMER,
            NegotiableQuoteInterface::STATUS_ORDERED,
            NegotiableQuoteInterface::STATUS_CLOSED,
        ],
        NegotiableQuoteInterface::STATUS_CLOSED => [],
    ];

    /**
     * @param NegotiableQuoteInterfaceFactory $negotiableQuoteFactory
     * @param NegotiableQuoteResource $negotiableQuoteResource
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     */
    public function __construct(
        NegotiableQuoteInterfaceFactory $negotiableQuoteFactory,
        NegotiableQuoteResource $negotiableQuoteResource,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
    ) {
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        $this->negotiableQuoteResource = $negotiableQuoteResource;
        $this->validatorResultFactory = $validatorResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $data)
    {
        $result = $this->validatorResultFactory->create();
        $negotiableQuote = $this->retrieveNegotiableQuote($data);
        if (empty($negotiableQuote)) {
            return $result;
        }
        $oldQuote = $this->negotiableQuoteFactory->create();
        $this->negotiableQuoteResource->load($oldQuote, $negotiableQuote->getQuoteId());

        $allowedStatuses = $this->allowChanges[$oldQuote->getStatus()];
        if ($negotiableQuote->hasData(NegotiableQuoteInterface::QUOTE_STATUS)
            && $negotiableQuote->getStatus() != $oldQuote->getStatus()
            && !in_array($negotiableQuote->getStatus(), $allowedStatuses)
        ) {
            $result->addMessage(__('You cannot update the quote status.'));
        }

        return $result;
    }

    /**
     * Retrieve negotiable quote from $data.
     *
     * @param array $data
     * @return NegotiableQuoteInterface
     */
    private function retrieveNegotiableQuote(array $data)
    {
        $negotiableQuote = !empty($data['negotiableQuote']) ? $data['negotiableQuote'] : null;
        if (!$negotiableQuote && !empty($data['quote']) && $data['quote']->getExtensionAttributes()
            && $data['quote']->getExtensionAttributes()->getNegotiableQuote()
            && $data['quote']->getExtensionAttributes()->getNegotiableQuote()->getIsRegularQuote()
        ) {
            $negotiableQuote = $data['quote']->getExtensionAttributes()->getNegotiableQuote();
        }

        return $negotiableQuote;
    }
}
