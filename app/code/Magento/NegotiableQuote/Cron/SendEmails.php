<?php
namespace Magento\NegotiableQuote\Cron;

use Magento\Store\Model\ScopeInterface;

/**
 * Send expiration emails.
 */
class SendEmails
{
    /**#@+
     * Configuration paths for email templates.
     */
    const CONFIG_QUOTE_EMAIL_NOTIFICATIONS_ENABLED = 'sales_email/quote/enabled';
    const EXPIRE_ONE_DAY_TEMPLATE = 'sales_email/quote/expire_one_day_template';
    const EXPIRE_TWO_DAYS_TEMPLATE = 'sales_email/quote/expire_two_days_template';
    /**#@-*/

    /**#@+
     * Email notification statuses.
     */
    const EMAIL_IS_NOT_SENT_COUNTER = 0;
    const EMAIL_SENT_TWO_DAYS_COUNTER = 1;
    const EMAIL_SENT_ONE_DAY_COUNTER = 2;
    /**#@-*/

    /**#@+
     * Timing for sending notifications.
     */
    const EMAIL_NOTIFICATION_TWO_DAYS = '+ 2 day';
    const EMAIL_NOTIFICATION_ONE_DAY = '+ 1 day';
    /**#@-*/

    /**
     * @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory
     */
    private $negotiableQuoteFactory;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\EmailSenderInterface
     */
    private $emailSender;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface
     */
    private $negotiableQuoteManagement;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory $negotiableQuoteFactory
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\NegotiableQuote\Model\EmailSenderInterface $emailSender
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterfaceFactory $negotiableQuoteFactory,
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\NegotiableQuote\Model\EmailSenderInterface $emailSender,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->negotiableQuoteFactory = $negotiableQuoteFactory;
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->emailSender = $emailSender;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->localeDate = $localeDate;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Method sends emails about quote expiration.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->scopeConfig->getValue(
            self::CONFIG_QUOTE_EMAIL_NOTIFICATIONS_ENABLED,
            ScopeInterface::SCOPE_STORE
        )) {
            return;
        }
        $this->sendNotification(
            self::EMAIL_NOTIFICATION_TWO_DAYS,
            self::EMAIL_IS_NOT_SENT_COUNTER,
            self::EMAIL_SENT_TWO_DAYS_COUNTER,
            self::EXPIRE_TWO_DAYS_TEMPLATE,
            true
        )->sendNotification(
            self::EMAIL_NOTIFICATION_ONE_DAY,
            self::EMAIL_SENT_TWO_DAYS_COUNTER,
            self::EMAIL_SENT_ONE_DAY_COUNTER,
            self::EXPIRE_ONE_DAY_TEMPLATE,
            false
        );
    }

    /**
     * Send Quote Expiration Notification (in 24 hrs and 48 hrs).
     *
     * @param string $days A date/time string.
     * @param int $counter
     * @param int $updateCounter
     * @param string $template
     * @param bool $merchant
     * @return $this
     */
    private function sendNotification($days, $counter, $updateCounter, $template, $merchant)
    {
        $quotes = $this->getQuotes($counter, $days);
        if ($quotes) {
            $this->sendEmailNotificationQuotes(
                $updateCounter,
                $template,
                $merchant,
                $quotes
            );
        }

        return $this;
    }

    /**
     * Send email notifications about quote expiration.
     *
     * @param string $emailSentCounter
     * @param string $template
     * @param bool $merchant
     * @param array $quotes [optional]
     * @return $this
     */
    private function sendEmailNotificationQuotes($emailSentCounter, $template, $merchant, array $quotes = [])
    {
        /** \Magento\Framework\Api\ExtensibleDataInterface[] $quotes */
        foreach ($quotes as $quote) {
            /** @var \Magento\Framework\Api\ExtensibleDataInterface $quote */
            $quoteId = $quote->getId();
            /** @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface $negotiableQuote */
            $negotiableQuote = $this->negotiableQuoteFactory->create();
            $negotiableQuote->setQuoteId($quoteId)->setEmailNotificationStatus($emailSentCounter);
            if ($this->negotiableQuoteRepository->save($negotiableQuote)) {
                if ($merchant) {
                    $this->emailSender->sendChangeQuoteEmailToMerchant(
                        $this->negotiableQuoteManagement->getNegotiableQuote($quoteId),
                        $template
                    );
                } else {
                    $this->emailSender->sendChangeQuoteEmailToBuyer(
                        $this->negotiableQuoteManagement->getNegotiableQuote($quoteId),
                        $template
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Get quotes without sent notifications that will expire in a day.
     *
     * @param int $statusEmailNotification
     * @param string $days [optional] A date/time string.
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    private function getQuotes($statusEmailNotification, $days = '')
    {
        $currentDate = $this->localeDate->date();
        $date = $currentDate->modify($days);
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('extension_attribute_negotiable_quote.expiration_period')
                    ->setConditionType('eq')
                    ->setValue($date->format('Y-m-d'))
                    ->create(),
            ]
        );
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('extension_attribute_negotiable_quote.status_email_notification')
                    ->setConditionType('eq')
                    ->setValue($statusEmailNotification)
                    ->create(),
            ]
        );
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $quotes = $this->negotiableQuoteRepository->getList($searchCriteria)->getItems();

        return $quotes;
    }
}
