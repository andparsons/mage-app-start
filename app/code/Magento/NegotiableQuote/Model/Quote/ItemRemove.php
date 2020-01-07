<?php

namespace Magento\NegotiableQuote\Model\Quote;

use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Model\Discount\StateChanges\Applier;
use Magento\NegotiableQuote\Model\HistoryManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for set notification and delete quote item from quote snapshot when product was deleted form catalog.
 *
 * @api
 * @since 100.0.0
 */
class ItemRemove
{
    /**
     * @var \Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface
     */
    private $negotiableQuoteRepository;

    /**
     * @var \Magento\NegotiableQuote\Model\Discount\StateChanges\Applier
     */
    private $messageApplier;

    /**
     * @var \Magento\NegotiableQuote\Model\HistoryManagementInterface
     */
    private $historyManagement;

    /**
     * Json Serializer instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Construct
     *
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param Applier $messageApplier
     * @param HistoryManagementInterface $historyManagement
     * @param Json $serializer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        Applier $messageApplier,
        HistoryManagementInterface $historyManagement,
        Json $serializer,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->negotiableQuoteRepository = $negotiableQuoteRepository;
        $this->messageApplier = $messageApplier;
        $this->historyManagement = $historyManagement;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Sets notifications and logs removed items.
     *
     * This method removes deleted SKUs from a negotiable quote snapshot and saves the SKUs so that they can be
     * displayed in adminhtml and frontend notifications. It also logs the "remove" event. If there are no removed SKUs
     * in the negotiable quote snapshot (which can occur if the negotiable quote is in the "draft" state), those SKUs
     * are saved only for adminhtml area notification.
     *
     * @param int $quoteId
     * @param int $productId
     * @param array $productSkus
     * @return $this
     * @throws LocalizedException
     */
    public function setNotificationRemove($quoteId, $productId, array $productSkus)
    {
        /** @var NegotiableQuoteInterface $negotiableQuote */
        $negotiableQuote = $this->negotiableQuoteRepository->getById($quoteId);
        if (!$negotiableQuote->getIsRegularQuote()) {
            return $this;
        }

        try {
            if (!$this->removeProductFromSnapshot($negotiableQuote, $productId)) {
                $skus = $this->getSkuString($negotiableQuote->getDeletedSku(), $productSkus, false);
                $negotiableQuote->setDeletedSku($skus);
            }
            $this->saveNegotiableQuote($negotiableQuote);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__('Cannot save removed quote item notification'));
        }

        $this->historyManagement->addItemRemoveCatalogLog($quoteId, $productId);

        return $this;
    }

    /**
     * Get new skus serialize string
     *
     * @param string $old
     * @param array $addSku
     * @param bool $isRemovedFromSnapshot
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getSkuString($old, array $addSku, $isRemovedFromSnapshot)
    {
        $arraySkus = [
            \Magento\Framework\App\Area::AREA_ADMINHTML => [],
            \Magento\Framework\App\Area::AREA_FRONTEND => []
        ];

        if (!empty($old)) {
            $arraySkus = $this->serializer->unserialize($old);
        }
        $arraySkus[\Magento\Framework\App\Area::AREA_ADMINHTML] = array_unique(
            array_merge($arraySkus[\Magento\Framework\App\Area::AREA_ADMINHTML], $addSku)
        );
        if ($isRemovedFromSnapshot) {
            $arraySkus[\Magento\Framework\App\Area::AREA_FRONTEND] = array_unique(
                array_merge($arraySkus[\Magento\Framework\App\Area::AREA_FRONTEND], $addSku)
            );
        }
        return $this->serializer->serialize($arraySkus);
    }

    /**
     * Remove product from shapshot quote
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param int $productId
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function removeProductFromSnapshot(
        NegotiableQuoteInterface $negotiableQuote,
        $productId
    ) {
        $snapshot = json_decode($negotiableQuote->getSnapshot(), true);
        if (!is_array($snapshot)) {
            return false;
        }
        $productSkus = [];
        foreach ($snapshot['items'] as $key => $item) {
            if ($item['product_id'] == $productId) {
                $productSkus[] = $item['sku'];
                unset($snapshot['items'][$key]);
            }
        }
        $this->setSnapshotDeletedSkus($negotiableQuote, $snapshot, $productSkus);

        return !empty($productSkus);
    }

    /**
     * Set snapshot deleted skus
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @param array $snapshot
     * @param array $productSkus
     * @return void
     * @throws \InvalidArgumentException
     *
     */
    private function setSnapshotDeletedSkus(
        NegotiableQuoteInterface $negotiableQuote,
        array $snapshot,
        array $productSkus
    ) {
        if (!empty($productSkus)) {
            $skus = $this->getSkuString($negotiableQuote->getDeletedSku(), $productSkus, true);
            $negotiableQuote->setDeletedSku($skus);
            $snapshot['negotiable_quote']['deleted_sku'] = $skus;
            $negotiableQuote->setSnapshot(json_encode($snapshot));
        }
    }

    /**
     * Save negotiable quote
     *
     * @param NegotiableQuoteInterface $negotiableQuote
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     */
    private function saveNegotiableQuote(NegotiableQuoteInterface $negotiableQuote)
    {
        $value = $negotiableQuote->getNegotiatedPriceValue();
        if ($value !== null) {
            $negotiableQuote->setHasUnconfirmedChanges(true);
            $negotiableQuote->setIsCustomerPriceChanged(true);
        }
        $this->negotiableQuoteRepository->save($negotiableQuote);
    }
}
