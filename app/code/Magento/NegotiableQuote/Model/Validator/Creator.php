<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResource;

/**
 * Validator for changing negotiable quote creator.
 */
class Creator implements ValidatorInterface
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
        $quoteData = $negotiableQuote->getData();

        if (isset($quoteData[NegotiableQuoteInterface::CREATOR_ID]) && $oldQuote->getCreatorId()
            && $quoteData[NegotiableQuoteInterface::CREATOR_ID] != $oldQuote->getCreatorId()
        ) {
            $result->addMessage(
                __(
                    'You cannot update the requested attribute. Row ID: %fieldName = %fieldValue.',
                    [
                        'fieldName' => NegotiableQuoteInterface::CREATOR_ID,
                        'fieldValue' => $quoteData[NegotiableQuoteInterface::CREATOR_ID]
                    ]
                )
            );
        }
        if (isset($quoteData[NegotiableQuoteInterface::CREATOR_TYPE]) && $oldQuote->getCreatorType()
            && $quoteData[NegotiableQuoteInterface::CREATOR_TYPE] != $oldQuote->getCreatorType()
        ) {
            $result->addMessage(
                __(
                    'You cannot update the requested attribute. Row ID: %fieldName = %fieldValue.',
                    [
                        'fieldName' => NegotiableQuoteInterface::CREATOR_TYPE,
                        'fieldValue' => $quoteData[NegotiableQuoteInterface::CREATOR_TYPE]
                    ]
                )
            );
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
