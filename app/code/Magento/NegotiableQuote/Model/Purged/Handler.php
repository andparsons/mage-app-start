<?php

namespace Magento\NegotiableQuote\Model\Purged;

/**
 * Class Handler processes necessary data of removed users and stores it.
 */
class Handler
{
    /**
     * @var \Magento\NegotiableQuote\Model\ResourceModel\PurgedContent
     */
    private $purgedContentResource;

    /**
     * @var \Magento\NegotiableQuote\Model\PurgedContentFactory
     */
    private $purgedContentFactory;

    /**
     * Handler constructor.
     *
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement
     * @param \Magento\NegotiableQuote\Model\ResourceModel\PurgedContent $purgedContentResource
     * @param \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory
     */
    public function __construct(
        \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        \Magento\NegotiableQuote\Api\NegotiableQuoteManagementInterface $negotiableQuoteManagement,
        \Magento\NegotiableQuote\Model\ResourceModel\PurgedContent $purgedContentResource,
        \Magento\NegotiableQuote\Model\PurgedContentFactory $purgedContentFactory
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->negotiableQuoteManagement = $negotiableQuoteManagement;
        $this->purgedContentResource = $purgedContentResource;
        $this->purgedContentFactory = $purgedContentFactory;
    }

    /**
     * Process removed user content.
     *
     * @param array $contentToStore
     * @param int $userId
     * @param bool $closeQuoteAfterProcessing
     * @return void
     */
    public function process(array $contentToStore, $userId, $closeQuoteAfterProcessing = true)
    {
        $quoteList = $this->negotiableQuoteRepository->getListByCustomerId($userId);

        foreach ($quoteList as $quote) {
            /** @var \Magento\NegotiableQuote\Model\PurgedContent $purgedContent */
            $purgedContent = $this->purgedContentFactory->create()->load($quote->getId());

            if (!$purgedContent->getQuoteId()) {
                $purgedContent->setQuoteId($quote->getId());
            }

            $toParse = $purgedContent->getPurgedData() ? $purgedContent->getPurgedData() : '';
            $toMerge = $toParse ? json_decode($toParse, true) : [];
            $encodedContentToStore = json_encode(array_replace_recursive($toMerge, $contentToStore));
            $purgedContent->setPurgedData($encodedContentToStore);
            $this->purgedContentResource->save($purgedContent);

            if ($closeQuoteAfterProcessing) {
                $this->negotiableQuoteManagement->close($quote->getId(), true);
            }
        }
    }
}
