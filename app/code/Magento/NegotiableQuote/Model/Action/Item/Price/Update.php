<?php

namespace Magento\NegotiableQuote\Model\Action\Item\Price;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\NegotiableQuote\Model\Quote\TotalsFactory;

/**
 * Class is responsible for updating custom price of quote items.
 */
class Update
{
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->localeFormat = $localeFormat;
        $this->serializer = $serializer;
    }

    /**
     * Update custom price of quote item.
     *
     * @param CartItemInterface $item
     * @param array $priceData
     * @return void
     */
    public function update(CartItemInterface $item, array $priceData)
    {
        if (isset($priceData['custom_price'])) {
            $itemPrice = $this->localeFormat->getNumber($priceData['custom_price']);
            $itemPrice = $itemPrice > 0 ? $itemPrice : 0;
            $this->modifyInfoBuyRequest($item, $itemPrice);
            $item->setCustomPrice($itemPrice);
            $item->setOriginalCustomPrice($itemPrice);
        } elseif ($item->hasData('custom_price')) {
            $this->modifyInfoBuyRequest($item, null);
            $item->unsetData('custom_price');
            $item->unsetData('original_custom_price');
        }
        $item->setNoDiscount(!isset($priceData['use_discount']));
    }

    /**
     * Change or remove custom price value in infoBuyRequest.
     *
     * @param CartItemInterface $item
     * @param float|int|null $customPrice
     * @return void
     */
    private function modifyInfoBuyRequest(CartItemInterface $item, $customPrice)
    {
        $infoBuyRequest = $item->getBuyRequest();
        if ($infoBuyRequest) {
            if ($customPrice !== null) {
                $infoBuyRequest->setCustomPrice($customPrice);
            } else {
                $infoBuyRequest->unsetData('custom_price');
            }
            $infoBuyRequest->setValue($this->serializer->serialize($infoBuyRequest->getData()));
            $infoBuyRequest->setCode('info_buyRequest');
            $infoBuyRequest->setProduct($item->getProduct());
            $item->addOption($infoBuyRequest);
        }
    }
}
