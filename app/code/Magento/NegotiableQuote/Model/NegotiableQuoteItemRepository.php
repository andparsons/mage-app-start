<?php

namespace Magento\NegotiableQuote\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\NegotiableQuote\Api\NegotiableQuoteItemRepositoryInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteItemInterfaceFactory;

/**
 * Company quote item repository object.
 */
class NegotiableQuoteItemRepository implements NegotiableQuoteItemRepositoryInterface
{
    /**
     * Company quote item resource
     *
     * @var ResourceModel\NegotiableQuoteItem $negotiableQuoteItemResource
     */
    protected $negotiableQuoteItemResource;

    /**
     * @param ResourceModel\NegotiableQuoteItem $negotiableQuoteItemResource
     */
    public function __construct(
        ResourceModel\NegotiableQuoteItem $negotiableQuoteItemResource
    ) {
        $this->negotiableQuoteItemResource = $negotiableQuoteItemResource;
    }

    /**
     * {@inheritdoc}
     */
    public function save(NegotiableQuoteItemInterface $quoteItem)
    {
        try {
            $this->negotiableQuoteItemResource->save($quoteItem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('There was an error saving negotiable quote item.'));
        }

        return true;
    }
}
