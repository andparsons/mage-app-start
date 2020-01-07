<?php

namespace Magento\NegotiableQuote\Controller\Quote;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\NegotiableQuote\Controller\Quote;
use Magento\Framework\App\Action\Context;
use Magento\NegotiableQuote\Helper\Quote as QuoteHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\NegotiableQuote\Model\Restriction\RestrictionInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class verifies if discounts were applied to the quote before sending it to the merchant.
 */
class CheckDiscount extends Quote implements HttpGetActionInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Context $context
     * @param QuoteHelper $quoteHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param RestrictionInterface $customerRestriction
     * @param NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Psr\Log\LoggerInterface $logger
     * @param Json $serializer
     */
    public function __construct(
        Context $context,
        QuoteHelper $quoteHelper,
        CartRepositoryInterface $quoteRepository,
        RestrictionInterface $customerRestriction,
        NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Psr\Log\LoggerInterface $logger,
        Json $serializer
    ) {
        parent::__construct(
            $context,
            $quoteHelper,
            $quoteRepository,
            $customerRestriction,
            $negotiableQuoteManagement,
            $settingsProvider
        );
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Check if quote has giftcards of promocodes.
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');
        $result = $this->settingsProvider->retrieveJsonError();

        if ($quoteId) {
            try {
                $quote = $this->quoteRepository->get($quoteId);
                $giftCards = $quote->getGiftCards() ? $this->serializer->unserialize($quote->getGiftCards()) : false;
                if (!empty($giftCards) || null !== $quote->getCouponCode()) {
                    $result = $this->settingsProvider->retrieveJsonSuccess(['discount' => true]);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->logger->critical($e);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('An error occurred while quote creation.'));
                $this->logger->critical($e);
            }
        }

        return $result;
    }
}
