<?php

namespace Magento\NegotiableQuote\Cron;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Model\Expiration;

/**
 * Quote expires means that the quote's expiration time is over. The buyer can edit the quote, but the merchant cannot.
 */
class ExpireQuote
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface
     */
    private $quoteGrid;

    /**
     * @var \Magento\NegotiableQuote\Model\Expiration
     */
    private $expiration;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * @var \Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList
     */
    private $expiredQuoteList;

    /**
     * @var \Magento\NegotiableQuote\Model\Expired\MerchantNotifier
     */
    private $merchantNotifier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface $quoteGrid
     * @param \Magento\NegotiableQuote\Model\Expiration $expiration
     * @param \Magento\NegotiableQuote\Model\HistoryManagementInterface $historyManagement
     * @param \Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList $expiredQuoteList
     * @param \Magento\NegotiableQuote\Model\Expired\MerchantNotifier $merchantNotifier
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\NegotiableQuote\Model\ResourceModel\QuoteGridInterface $quoteGrid,
        \Magento\NegotiableQuote\Model\Expiration $expiration,
        \Magento\NegotiableQuote\Model\HistoryManagementInterface $historyManagement,
        \Magento\NegotiableQuote\Model\Expired\Provider\ExpiredQuoteList $expiredQuoteList,
        \Magento\NegotiableQuote\Model\Expired\MerchantNotifier $merchantNotifier,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->quoteGrid = $quoteGrid;
        $this->expiration = $expiration;
        $this->historyManagement = $historyManagement;
        $this->expiredQuoteList = $expiredQuoteList;
        $this->merchantNotifier = $merchantNotifier;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Cron job method to set 'expired' status for quotes.
     *
     * @return void
     */
    public function execute()
    {
        try {
            foreach ($this->expiredQuoteList->getExpiredQuotes() as $quote) {
                $quoteId = $quote->getId();
                /** @var \Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface $negotiableQuote */
                $negotiableQuote = $quote->getExtensionAttributes()->getNegotiableQuote();

                if ($this->canExtendExpiration($negotiableQuote->getStatus())) {
                    $date = $this->getExtendedExpirationPeriod();
                    $negotiableQuote->setQuoteId($quoteId)->setExpirationPeriod($date);
                    $this->merchantNotifier->sendNotification($quoteId);
                } else {
                    $negotiableQuote->setQuoteId($quoteId)
                        ->setStatus(NegotiableQuoteInterface::STATUS_EXPIRED)
                        ->setShippingPrice(null);
                    $snapshot = $this->serializer->unserialize($negotiableQuote->getSnapshot());
                    $snapshot['negotiable_quote'][NegotiableQuoteInterface::QUOTE_STATUS]
                        = NegotiableQuoteInterface::STATUS_EXPIRED;
                    $negotiableQuote->setSnapshot($this->serializer->serialize($snapshot));
                }

                $saveResult = $this->negotiableQuoteRepository->save($negotiableQuote);

                if ($saveResult) {
                    $this->updateExpiredQuoteStatusInLogs($negotiableQuote->getQuoteId());
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Quote can't expire while it is processing by admin. Expiration period is only extended.
     * This method checks that status corresponds to processing by admin.
     *
     * @param string $status
     * @return bool
     */
    private function canExtendExpiration($status)
    {
        return in_array(
            $status,
            [
                NegotiableQuoteInterface::STATUS_PROCESSING_BY_ADMIN,
                NegotiableQuoteInterface::STATUS_CREATED,
                NegotiableQuoteInterface::STATUS_SUBMITTED_BY_CUSTOMER,
            ]
        );
    }

    /**
     * Update quote status to expired grid and logs.
     *
     * @param int $expiredQuoteId
     * @return void
     */
    private function updateExpiredQuoteStatusInLogs($expiredQuoteId)
    {
        $this->quoteGrid->refreshValue(
            'entity_id',
            $expiredQuoteId,
            'status',
            NegotiableQuoteInterface::STATUS_EXPIRED
        );
        $this->historyManagement->updateStatusLog($expiredQuoteId, false, true);
    }

    /**
     * Get extended expiration period based on default expiration period.
     *
     * @return string
     */
    private function getExtendedExpirationPeriod()
    {
        $expirationDate = $this->expiration->retrieveDefaultExpirationDate();
        $formattedExpirationDate = $expirationDate === null
            ? Expiration::DATE_QUOTE_NEVER_EXPIRES
            : $expirationDate->format('Y-m-d');

        return $formattedExpirationDate;
    }
}
