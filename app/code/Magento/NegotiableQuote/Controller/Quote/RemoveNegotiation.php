<?php

namespace Magento\NegotiableQuote\Controller\Quote;

/**
 * Class RemoveNegotiation
 */
class RemoveNegotiation extends \Magento\NegotiableQuote\Controller\Quote
{
    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\NegotiableQuote\Helper\Quote $quoteHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\NegotiableQuote\Helper\Quote $quoteHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\NegotiableQuote\Model\Restriction\RestrictionInterface $customerRestriction,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\SettingsProvider $settingsProvider,
        \Psr\Log\LoggerInterface $logger
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
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $quoteId = $this->getRequest()->getParam('quote_id');
        $response = $this->settingsProvider->retrieveJsonSuccess(['removed' => true]);

        if ($quoteId) {
            try {
                $this->negotiableQuoteManagement->removeNegotiation($quoteId);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $response = $this->settingsProvider->retrieveJsonError();
            }
        }

        return $response;
    }
}
