<?php

namespace Magento\NegotiableQuote\Model\Validator;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\NegotiableQuote\Model\Expiration;
use Magento\NegotiableQuote\Model\ResourceModel\NegotiableQuote as NegotiableQuoteResource;

/**
 * Validator for expiration date in negotiable quote.
 */
class ExpirationDate implements ValidatorInterface
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
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @param NegotiableQuoteInterfaceFactory $negotiableQuoteFactory
     * @param NegotiableQuoteResource $negotiableQuoteResource
     * @param \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    public function __construct(
        NegotiableQuoteInterfaceFactory $negotiableQuoteFactory,
        NegotiableQuoteResource $negotiableQuoteResource,
        \Magento\NegotiableQuote\Model\Validator\ValidatorResultFactory $validatorResultFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
    ) {
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        $this->negotiableQuoteResource = $negotiableQuoteResource;
        $this->validatorResultFactory = $validatorResultFactory;
        $this->localeDate = $localeDate;
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

        if ($negotiableQuote->getExpirationPeriod()
            && $negotiableQuote->getExpirationPeriod() != $oldQuote->getExpirationPeriod()
        ) {
            if ($negotiableQuote->getExpirationPeriod() === Expiration::DATE_QUOTE_NEVER_EXPIRES) {
                return $result;
            }

            $format = DateTime::DATE_PHP_FORMAT;
            $date = \DateTime::createFromFormat($format, $negotiableQuote->getExpirationPeriod());
            if (empty($date)) {
                $result->addMessage(
                    __(
                        'The expiration date is in the wrong format. The correct format is %dateformat.',
                        ['dateformat' => $format]
                    )
                );
                return $result;
            }
            $now = $this->localeDate->date(null, null, false);
            $now->setTime(0, 0, 0);
            $date->setTime(0, 0, 0);
            if ($now > $date) {
                $result->addMessage(
                    __('Cannot update the expiration date. You must specify today\'s date or a future date.')
                );
            }
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
