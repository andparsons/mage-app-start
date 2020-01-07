<?php

namespace Magento\NegotiableQuote\Model\Webapi\GiftCardAccount;

use Magento\NegotiableQuote\Api\GiftCardAccountManagementInterface;

/**
 * Class GiftCardAccountManagement
 */
class GiftCardAccountManagement implements GiftCardAccountManagementInterface
{
    /**
     * @var \Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface
     */
    private $originalInterface;

    /**
     * @var \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator
     */
    private $validator;

    /**
     * @param \Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface $originalInterface
     * @param \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
     */
    public function __construct(
        \Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface $originalInterface,
        \Magento\NegotiableQuote\Model\Webapi\CustomerCartValidator $validator
    ) {
        $this->originalInterface = $originalInterface;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByQuoteId($cartId, $giftCardCode)
    {
        $this->validator->validate($cartId);
        return $this->originalInterface->deleteByQuoteId($cartId, $giftCardCode);
    }

    /**
     * {@inheritdoc}
     */
    public function saveByQuoteId(
        $cartId,
        \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface $giftCardAccountData
    ) {
        $this->validator->validate($cartId);
        return $this->originalInterface->saveByQuoteId($cartId, $giftCardAccountData);
    }
}
